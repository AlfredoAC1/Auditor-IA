<?php

declare(strict_types=1);

namespace App\Services\LLM;

use App\Enums\AnalysisType;
use App\Models\Document;
use App\Models\DocumentAnalysis;

/**
 * Builds analysis prompts for all LLM providers.
 *
 * Centralises the Chilean legal context, analysis-type schemas,
 * and user/contextual prompt construction so that every provider
 * adapter reuses the exact same wording — ensuring consistent
 * results across Groq, Cerebras, Qwen Cloud, etc.
 */
final class LlmPromptBuilder
{
    // ---------------------------------------------------------------
    // Public builders
    // ---------------------------------------------------------------

    /**
     * Build the full system prompt for a given analysis type.
     */
    public function buildSystemPrompt(AnalysisType $analysisType): string
    {
        return $this->baseContext().$this->analysisInstructions($analysisType);
    }

    /**
     * Build a simple user prompt from document content.
     */
    public function buildUserPrompt(
        string $documentContent,
        AnalysisType $analysisType,
        Document $document,
    ): string {
        $contextParts = [];

        if ($document->document_type !== null) {
            $contextParts[] = "Tipo de documento declarado: {$document->document_type->label()}";
        }

        if ($document->original_filename !== null && $document->original_filename !== '') {
            $contextParts[] = "Nombre del archivo: {$document->original_filename}";
        }

        $contextHeader = $contextParts !== []
            ? "CONTEXTO:\n".implode("\n", $contextParts)."\n\n"
            : '';

        return <<<PROMPT
{$contextHeader}DOCUMENTO A ANALIZAR:

{$documentContent}

Por favor, realiza un análisis de tipo "{$analysisType->label()}" de este documento, siguiendo estrictamente el formato JSON indicado en las instrucciones del sistema.
PROMPT;
    }

    /**
     * Build an enriched user prompt for contextual multi-document analysis.
     *
     * @param  array<int, array{filename: string, content: string}>  $supportingParts
     */
    public function buildContextualUserPrompt(
        Document $primaryDocument,
        string $primaryContent,
        DocumentAnalysis $existingAnalysis,
        array $supportingParts,
        AnalysisType $analysisType,
    ): string {
        // Primary document block
        $primaryHeader = "DOCUMENTO PRINCIPAL: {$primaryDocument->original_filename}";
        if ($primaryDocument->document_type !== null) {
            $primaryHeader .= "\nTipo: {$primaryDocument->document_type->label()}";
        }

        // Prior analysis summary
        $priorContent = $existingAnalysis->content;
        $priorSummary = '';

        if (isset($priorContent['resumen_general'])) {
            $priorSummary .= "Resumen previo: {$priorContent['resumen_general']}\n";
        }
        if (isset($priorContent['tipo_documento'])) {
            $priorSummary .= "Tipo identificado previamente: {$priorContent['tipo_documento']}\n";
        }
        if (isset($priorContent['nivel_confianza'])) {
            $priorSummary .= "Nivel de confianza previo: {$priorContent['nivel_confianza']}\n";
        }

        if ($priorSummary === '') {
            $priorSummary = 'Análisis previo disponible (ver prompt_metadata para referencia).';
        }

        // Supporting documents block
        $supportingBlock = '';
        if ($supportingParts !== []) {
            $supportingBlock = "\n\n".str_repeat('=', 60);
            $supportingBlock .= "\nDOCUMENTOS DE APOYO (".count($supportingParts).")\n";
            $supportingBlock .= str_repeat('=', 60);
            foreach ($supportingParts as $i => $part) {
                $num = $i + 1;
                $supportingBlock .= "\n\n--- DOCUMENTO DE APOYO #{$num}: {$part['filename']} ---\n\n{$part['content']}";
            }
        }

        $sep = str_repeat('=', 60);
        $type = $analysisType->label();

        return <<<PROMPT
CONTEXTO DEL ANÁLISIS:
Este es un análisis de tipo "{$type}" que incorpora el contexto de un análisis previo y documentos de apoyo adicionales.

{$primaryHeader}

--- CONTENIDO DEL DOCUMENTO PRINCIPAL ---

{$primaryContent}

{$sep}
ANÁLISIS PREVIO (resumen)
{$sep}

{$priorSummary}{$supportingBlock}

{$sep}
INSTRUCCIÓN
{$sep}
Considerando el documento principal, el análisis previo, y todos los documentos de apoyo adjuntos, realiza un análisis de tipo "{$type}" integrado. Identifica relaciones, contradicciones, complementariedades o aclaraciones entre los documentos. Responde siguiendo estrictamente el formato JSON indicado en las instrucciones del sistema.
PROMPT;
    }

    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    private function baseContext(): string
    {
        return <<<'PROMPT'
Eres un asistente legal especializado en legislación chilena. Tu rol es analizar documentos legales y proporcionar información clara, precisa y útil para personas sin formación jurídica.

CONTEXTO LEGAL:
- Todas tus respuestas deben basarse en el marco legal chileno vigente.
- Referencia leyes específicas cuando sea relevante (Código Civil, Código del Trabajo, Ley de Protección al Consumidor, etc.).
- Si identificas cláusulas que podrían ser abusivas según la legislación chilena, menciónalo explícitamente.
- Indica siempre cuando una situación requiere consulta profesional con un abogado.

REGLAS DE RESPUESTA:
- Responde SIEMPRE en español chileno.
- Usa lenguaje simple y claro, evitando jerga legal innecesaria.
- Cuando uses términos legales, explícalos brevemente entre paréntesis.
- Estructura tu respuesta estrictamente en el formato JSON especificado.
- Proporciona análisis completos y detallados; no trunces información relevante.
- Limita los arreglos a un máximo razonable de 10-15 ítems por campo para mantener el foco.
- NO inventes leyes ni artículos que no existan.
- Si no tienes certeza sobre un punto legal, indícalo explícitamente.
- NUNCA des asesoría legal definitiva — siempre recomienda consultar con un abogado para decisiones importantes.
PROMPT;
    }

    private function analysisInstructions(AnalysisType $analysisType): string
    {
        return match ($analysisType) {
            AnalysisType::FullSummary => <<<'PROMPT'

TIPO DE ANÁLISIS: Resumen Completo
Proporciona un análisis integral del documento que incluya todas las secciones.
Responde en formato JSON con la siguiente estructura:
{
  "resumen_general": "Descripción general del documento en 2-3 párrafos",
  "tipo_documento": "Tipo de documento identificado",
  "partes_involucradas": ["Lista de partes mencionadas en el documento"],
  "clausulas_principales": [
    {
      "numero": "Número o identificador de la cláusula",
      "titulo": "Título o tema de la cláusula",
      "explicacion": "Explicación en lenguaje simple",
      "implicacion_legal": "Qué significa legalmente para las partes"
    }
  ],
  "riesgos_identificados": [
    {
      "riesgo": "Descripción del riesgo",
      "severidad": "alta|media|baja",
      "explicacion": "Por qué es un riesgo",
      "referencia_legal": "Ley o artículo relevante de la legislación chilena"
    }
  ],
  "recomendaciones": [
    {
      "recomendacion": "Acción sugerida",
      "prioridad": "alta|media|baja",
      "justificacion": "Por qué se recomienda esto"
    }
  ],
  "leyes_aplicables": ["Lista de leyes chilenas relevantes al documento"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo",
  "notas_adicionales": "Cualquier observación adicional relevante"
}
PROMPT,

            AnalysisType::ClauseAnalysis => <<<'PROMPT'

TIPO DE ANÁLISIS: Análisis de Cláusulas
Analiza cada cláusula del documento individualmente.
Responde en formato JSON con la siguiente estructura:
{
  "tipo_documento": "Tipo de documento identificado",
  "total_clausulas": 0,
  "clausulas": [
    {
      "numero": "Número o identificador",
      "titulo": "Tema de la cláusula",
      "texto_original_resumido": "Resumen breve del texto original",
      "explicacion_simple": "Qué significa en lenguaje cotidiano",
      "es_estandar": true,
      "es_potencialmente_abusiva": false,
      "referencia_legal": "Ley chilena aplicable si corresponde",
      "alerta": "Punto de atención si existe, null si no"
    }
  ],
  "clausulas_faltantes": ["Cláusulas que deberían estar pero no aparecen, según la legislación chilena"],
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT,

            AnalysisType::RiskAssessment => <<<'PROMPT'

TIPO DE ANÁLISIS: Evaluación de Riesgos
Identifica y evalúa los riesgos legales presentes en el documento.
Responde en formato JSON con la siguiente estructura:
{
  "tipo_documento": "Tipo de documento identificado",
  "nivel_riesgo_general": "alto|medio|bajo",
  "riesgos": [
    {
      "id": 1,
      "titulo": "Título del riesgo",
      "descripcion": "Descripción detallada",
      "severidad": "alta|media|baja",
      "probabilidad": "alta|media|baja",
      "clausula_relacionada": "Referencia a la cláusula del documento",
      "referencia_legal": "Ley chilena que aplica",
      "recomendacion": "Qué hacer al respecto",
      "requiere_accion_inmediata": false
    }
  ],
  "fortalezas": ["Aspectos positivos del documento"],
  "debilidades": ["Aspectos que necesitan atención"],
  "recomendacion_general": "Resumen de qué debería hacer el usuario",
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT,

            AnalysisType::Recommendation => <<<'PROMPT'

TIPO DE ANÁLISIS: Recomendaciones
Proporciona recomendaciones prácticas basadas en el contenido del documento.
Responde en formato JSON con la siguiente estructura:
{
  "tipo_documento": "Tipo de documento identificado",
  "resumen_breve": "Resumen del documento en 1-2 oraciones",
  "recomendaciones": [
    {
      "id": 1,
      "titulo": "Título de la recomendación",
      "descripcion": "Explicación detallada de la recomendación",
      "prioridad": "alta|media|baja",
      "categoria": "legal|financiero|administrativo|proteccion",
      "accion_sugerida": "Paso concreto que el usuario debería tomar",
      "referencia_legal": "Ley chilena relevante si aplica"
    }
  ],
  "puntos_a_negociar": ["Puntos que podrían negociarse antes de firmar"],
  "documentos_complementarios": ["Documentos adicionales que el usuario debería solicitar"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT,

            AnalysisType::Explanation => <<<'PROMPT'

TIPO DE ANÁLISIS: Explicación Simple
Explica el documento en lenguaje cotidiano para una persona sin conocimientos legales.
Responde en formato JSON con la siguiente estructura:
{
  "tipo_documento": "Tipo de documento identificado",
  "explicacion_general": "Explicación del documento como si se lo explicaras a un amigo, en 2-3 párrafos",
  "que_significa_para_ti": "Qué implica este documento para el usuario en términos prácticos",
  "puntos_clave": [
    {
      "punto": "Punto importante",
      "explicacion": "Qué significa en la práctica"
    }
  ],
  "obligaciones_del_usuario": ["Lista de obligaciones que asume quien firma"],
  "derechos_del_usuario": ["Lista de derechos que tiene quien firma"],
  "fechas_importantes": [
    {
      "fecha_o_plazo": "Fecha o plazo mencionado",
      "que_sucede": "Qué pasa en esa fecha"
    }
  ],
  "montos_o_valores": [
    {
      "concepto": "Concepto del monto",
      "valor": "Valor mencionado",
      "nota": "Nota relevante si aplica"
    }
  ],
  "preguntas_frecuentes": [
    {
      "pregunta": "Pregunta que el usuario podría tener",
      "respuesta": "Respuesta clara y simple"
    }
  ],
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT,

            AnalysisType::ContextualSummary => <<<'PROMPT'

TIPO DE ANÁLISIS: Análisis Contextual con Documentos de Apoyo
Este análisis integra el documento principal con documentos de apoyo adicionales y el análisis previo.
Identifica relaciones, contradicciones, complementariedades y aclaraciones entre todos los documentos.
Responde en formato JSON con la siguiente estructura:
{
  "tipo_documento": "Tipo del documento principal",
  "resumen_integrado": "Resumen que integra todos los documentos analizados en 2-3 párrafos",
  "relaciones_entre_documentos": [
    {
      "documento_a": "Nombre del primer documento",
      "documento_b": "Nombre del segundo documento",
      "tipo_relacion": "complementa|contradice|aclara|referencia|modifica",
      "descripcion": "Explicación de la relación entre ambos documentos"
    }
  ],
  "puntos_clave_integrados": [
    {
      "punto": "Punto relevante que emerge de la lectura conjunta",
      "fuente": "Documento o documentos de los que surge",
      "importancia": "alta|media|baja"
    }
  ],
  "contradicciones": [
    {
      "descripcion": "Descripción de la contradicción o inconsistencia",
      "documentos_involucrados": ["Nombres de los documentos involucrados"],
      "recomendacion": "Qué debería hacer el usuario al respecto"
    }
  ],
  "riesgos_identificados": [
    {
      "riesgo": "Descripción del riesgo",
      "severidad": "alta|media|baja",
      "referencia_legal": "Ley chilena aplicable",
      "documento_origen": "Documento del que surge el riesgo"
    }
  ],
  "recomendaciones_finales": [
    {
      "recomendacion": "Acción sugerida",
      "prioridad": "alta|media|baja",
      "justificacion": "Por qué se recomienda"
    }
  ],
  "documentos_analizados": ["Lista de nombres de todos los documentos considerados"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo",
  "notas_adicionales": "Observaciones relevantes sobre la coherencia del conjunto de documentos"
}
PROMPT,
        };
    }
}
