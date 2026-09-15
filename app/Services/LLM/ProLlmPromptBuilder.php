<?php

declare(strict_types=1);

namespace App\Services\LLM;

use App\Enums\AiModelTier;
use App\Enums\AnalysisType;
use App\Models\Document;
use App\Models\DocumentAnalysis;

/**
 * Builds scaled analysis prompts for Pro & Lifetime users running on Qwen Cloud.
 *
 * Tailors prompt depth, analytical rigor, and requested JSON schema complexity
 * to the selected AiModelTier:
 *   - Lightweight (Qwen3 14B): Fast, high-signal contract scan (1x credits)
 *   - Standard    (Qwen3 Max): Comprehensive clause & statutory audit (2x credits)
 *   - Advanced    (Qwen3.5 Flash): Deep asymmetry, loophole & counter-clause analysis (3x credits)
 *   - Premium     (Qwen-3.7 Flash): Forensic institutional audit, clause redlining & scenario stress-testing (4x credits)
 */
final class ProLlmPromptBuilder
{
    /**
     * Build system prompt scaled by model tier and analysis type.
     */
    public function buildSystemPrompt(
        AnalysisType $analysisType,
        AiModelTier $tier = AiModelTier::Standard,
    ): string {
        return $this->baseLegalContext($tier)
            .$this->tierSpecificDirective($tier)
            .$this->analysisInstructions($analysisType, $tier);
    }

    /**
     * Build user prompt with document context and tier directives.
     */
    public function buildUserPrompt(
        string $documentContent,
        AnalysisType $analysisType,
        Document $document,
        AiModelTier $tier = AiModelTier::Standard,
    ): string {
        $contextParts = [];

        if ($document->document_type !== null) {
            $contextParts[] = "Tipo de documento declarado: {$document->document_type->label()}";
        }

        if ($document->original_filename !== null && $document->original_filename !== '') {
            $contextParts[] = "Nombre del archivo: {$document->original_filename}";
        }

        $contextParts[] = "Nivel de auditoría contratado: {$tier->label()} ({$tier->description()})";

        $contextHeader = "CONTEXTO DEL DOCUMENTO:\n".implode("\n", $contextParts)."\n\n";

        $depthInstructions = match ($tier) {
            AiModelTier::Lightweight => 'Entrega un análisis conciso, directo y estructurado, identificando con claridad los puntos más críticos sin extenderte innecesariamente.',
            AiModelTier::Standard => 'Realiza un análisis exhaustivo y detallado, revisando cláusula por cláusula y respaldando tus observaciones con la normativa legal chilena aplicable.',
            AiModelTier::Advanced => 'Aplica un razonamiento jurídico avanzado: identifica asimetrías de poder contractual, vacíos normativos, ambigüedades de redacción y plantea estrategias concretas de mitigación.',
            AiModelTier::Premium => 'Ejecuta una auditoría forense institucional de máxima profundidad: incluye redlining de cláusulas abusivas con redacción sugerida alternativa, evalúa escenarios de contingencia adversa y referencia doctrina jurisprudencial chilena.',
        };

        return <<<PROMPT
{$contextHeader}DIRECTIVA DE PROFUNDIDAD ({$tier->label()}):
{$depthInstructions}

DOCUMENTO A AUDITAR:
{$documentContent}

INSTRUCCIÓN FINAL:
Por favor, genera la auditoría de tipo "{$analysisType->label()}" siguiendo estrictamente el esquema JSON indicado en las instrucciones del sistema, con el nivel de detalle y rigor correspondiente al nivel {$tier->label()}.
PROMPT;
    }

    /**
     * Build contextual user prompt for multi-document audits scaled by tier.
     *
     * @param  array<int, array{filename: string, content: string}>  $supportingParts
     */
    public function buildContextualUserPrompt(
        Document $primaryDocument,
        string $primaryContent,
        DocumentAnalysis $existingAnalysis,
        array $supportingParts,
        AnalysisType $analysisType = AnalysisType::ContextualSummary,
        AiModelTier $tier = AiModelTier::Standard,
    ): string {
        $primaryHeader = "DOCUMENTO PRINCIPAL: {$primaryDocument->original_filename}";
        if ($primaryDocument->document_type !== null) {
            $primaryHeader .= "\nTipo: {$primaryDocument->document_type->label()}";
        }

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
            $priorSummary = 'Análisis previo disponible.';
        }

        $supportingBlock = '';
        if ($supportingParts !== []) {
            $supportingBlock = "\n\n".str_repeat('=', 60);
            $supportingBlock .= "\nDOCUMENTOS DE APOYO Y ANEXOS (".count($supportingParts).")\n";
            $supportingBlock .= str_repeat('=', 60);
            foreach ($supportingParts as $i => $part) {
                $num = $i + 1;
                $supportingBlock .= "\n\n--- ANEXO #{$num}: {$part['filename']} ---\n\n{$part['content']}";
            }
        }

        $sep = str_repeat('=', 60);
        $type = $analysisType->label();

        return <<<PROMPT
AUDITORÍA CONTEXTUAL INTEGRADA ({$tier->label()}):
Este análisis integra el documento principal con anexos/documentos de apoyo y el análisis anterior.

{$primaryHeader}

--- CONTENIDO DEL DOCUMENTO PRINCIPAL ---
{$primaryContent}

{$sep}
ANÁLISIS PREVIO DE REFERENCIA
{$sep}
{$priorSummary}{$supportingBlock}

{$sep}
INSTRUCCIÓN DE AUDITORÍA CONJUNTA ({$tier->label()})
{$sep}
Analiza la coherencia, prevalencia jurídica (orden de prelación), modificaciones tácitas o expresas, contradicciones y adiciones que los documentos de apoyo introducen sobre el documento principal.
Genera la respuesta estrictamente en formato JSON con la profundidad y rigor del nivel {$tier->label()}.
PROMPT;
    }

    // ---------------------------------------------------------------
    // Private: Context & Directives
    // ---------------------------------------------------------------

    private function baseLegalContext(AiModelTier $tier): string
    {
        return <<<'PROMPT'
Eres AuditorIA Pro, un sistema experto de inteligencia jurídica especializado en derecho chileno (República de Chile). Tu propósito es auditar, analizar y evaluar instrumentos jurídicos con los más altos estándares profesionales.

MARCO LEGAL VIGENTE (CHILE):
- Aplica rigurosamente el ordenamiento jurídico chileno: Código Civil, Código de Comercio, Código del Trabajo, Ley 19.496 de Protección de los Derechos de los Consumidores, Ley 18.101 de Arrendamiento de Predios Urbanos, Ley 20.720 de Insolvencia y Reemprendimiento, y leyes especiales según la materia.
- Si detectas cláusulas abusivas, nulas por objeto ilícito (Art. 1466 CC), o ineficaces bajo la legislación chilena, indícalo de manera precisa y categórica.
- Distingue claramente entre normas de orden público (inderogables por voluntad de las partes) y normas dispositivas.
- Responde SIEMPRE en español chileno profesional, claro, riguroso y fundamentado.
- Tu respuesta debe ser EXCLUSIVAMENTE un objeto JSON válido, sin bloques de código markdown añadidos en el exterior ni comentarios fuera del JSON.

PROMPT;
    }

    private function tierSpecificDirective(AiModelTier $tier): string
    {
        return match ($tier) {
            AiModelTier::Lightweight => <<<'PROMPT'
NIVEL DE SERVICIO: LIGHTWEIGHT (Qwen3 14B)
- Prioridad: Claridad, rapidez, concisión y foco en aspectos de alto impacto.
- Identifica los 3 a 5 riesgos esenciales y las obligaciones contractuales nucleares.
- Redacta explicaciones directas en 1-2 párrafos por sección.
- Genera un JSON compacto y altamente informativo.

PROMPT,

            AiModelTier::Standard => <<<'PROMPT'
NIVEL DE SERVICIO: STANDARD (Qwen3 Max)
- Prioridad: Auditoría jurídica balanceada y completa.
- Revisa detalladamente hasta 10 cláusulas principales con su respectivo análisis de riesgo.
- Incluye citas expresas a leyes y artículos del derecho chileno aplicable.
- Desarrolla listas de verificación para negociación previa a la firma y alertas de desbalance obligacional.
- Proporciona un JSON de profundidad media-alta.

PROMPT,

            AiModelTier::Advanced => <<<'PROMPT'
NIVEL DE SERVICIO: ADVANCED (Qwen3.5 Flash)
- Prioridad: Análisis profundo de asimetría contractual, blindaje jurídico y detección de vacíos legales.
- Examina cláusulas de limitación de responsabilidad, techos indemnizatorios, cláusulas penales excesivas (Art. 1544 CC) y fueros de resolución de controversias (tribunales ordinarios vs tribunales arbitrales CAM Santiago).
- Identifica riesgos silenciosos (omisiones de plazos de prescripción, indemnizaciones cruzadas, renuncias anticipadas de derechos).
- Propone formulaciones alternativas y contrapropuestas tácticas de negociación.
- Proporciona un JSON altamente detallado y de gran extensión.

PROMPT,

            AiModelTier::Premium => <<<'PROMPT'
NIVEL DE SERVICIO: PREMIUM (Qwen-3.7 Flash - Frontier)
- Prioridad: Auditoría forense institucional exhaustiva, redlining de cláusulas y simulación de escenarios adversos.
- Ejecuta una disección forense integral del instrumento legal.
- REDLINING CLÁUSULA POR CLÁUSULA: Para cada cláusula riesgosa o abusiva, proporciona la redacción actual, el diagnóstico de riesgo y la redacción alternativa sugerida en lenguaje legal chileno ("clausula_sugerida").
- SIMULACIÓN DE ESCENARIOS: Evalúa el impacto del contrato bajo 3 escenarios críticos: (a) Incumplimiento grave de contraparte, (b) Liquidación o insolvencia concursal de contraparte (Ley 20.720), (c) Cambio regulatorio o fuerza mayor imprevista (teoría de la imprevisión en sede judicial chilena).
- DOCTRINA JURISPRUDENCIAL: Menciona la tendencia de la Corte Suprema o Cortes de Apelaciones chilenas sobre las cláusulas clave.
- BRIEFING EJECUTIVO: Redacta un memorándum de recomendaciones estratégicas para la gerencia general y equipo legal.
- Proporciona un JSON de máxima extensión y profundidad técnica.

PROMPT,
        };
    }

    private function analysisInstructions(AnalysisType $analysisType, AiModelTier $tier): string
    {
        return match ($tier) {
            AiModelTier::Lightweight => $this->lightweightSchemas($analysisType),
            AiModelTier::Standard => $this->standardSchemas($analysisType),
            AiModelTier::Advanced => $this->advancedSchemas($analysisType),
            AiModelTier::Premium => $this->premiumSchemas($analysisType),
        };
    }

    // ---------------------------------------------------------------
    // Private: Schemas by Tier
    // ---------------------------------------------------------------

    private function lightweightSchemas(AnalysisType $analysisType): string
    {
        return match ($analysisType) {
            AnalysisType::FullSummary => <<<'PROMPT'
ESTRUCTURA DE RESPUESTA JSON (Lightweight):
{
  "resumen_general": "Resumen ejecutivo del documento en 1-2 párrafos",
  "tipo_documento": "Tipo de contrato o documento identificado",
  "partes_involucradas": ["Parte 1", "Parte 2"],
  "clausulas_principales": [
    {
      "numero": "Cláusula X",
      "titulo": "Tema",
      "explicacion": "Explicación simple",
      "implicacion_legal": "Consecuencia principal"
    }
  ],
  "riesgos_identificados": [
    {
      "riesgo": "Descripción del riesgo",
      "severidad": "alta|media|baja",
      "explicacion": "Por qué es riesgoso",
      "referencia_legal": "Ley chilena aplicable"
    }
  ],
  "recomendaciones": [
    {
      "recomendacion": "Acción recomendada",
      "prioridad": "alta|media|baja",
      "justificacion": "Motivo"
    }
  ],
  "leyes_aplicables": ["Leyes chilenas relevantes"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT,

            default => $this->lightweightGenericSchema($analysisType),
        };
    }

    private function standardSchemas(AnalysisType $analysisType): string
    {
        return match ($analysisType) {
            AnalysisType::FullSummary => <<<'PROMPT'
ESTRUCTURA DE RESPUESTA JSON (Standard):
{
  "resumen_general": "Resumen analítico completo del documento en 2-3 párrafos",
  "tipo_documento": "Tipo de contrato identificado con precisión",
  "partes_involucradas": ["Lista detallada con calidades jurídicas (arrendador, mandatario, etc.)"],
  "clausulas_principales": [
    {
      "numero": "Cláusula X",
      "titulo": "Tema de la cláusula",
      "explicacion": "Explicación clara en términos prácticos",
      "implicacion_legal": "Consecuencias jurídicas y patrimoniales",
      "es_estandar": true,
      "alerta": "Punto de atención si existe"
    }
  ],
  "riesgos_identificados": [
    {
      "riesgo": "Descripción detallada del riesgo jurídico o patrimonial",
      "severidad": "alta|media|baja",
      "probabilidad": "alta|media|baja",
      "clausula_relacionada": "Cláusula vinculada",
      "referencia_legal": "Artículo y ley de la legislación chilena",
      "explicacion": "Análisis del riesgo y posible impacto"
    }
  ],
  "recomendaciones": [
    {
      "recomendacion": "Acción preventiva o correctiva",
      "prioridad": "alta|media|baja",
      "justificacion": "Fundamento legal y práctico",
      "momento": "antes_de_firmar|durante_vigencia|al_termino"
    }
  ],
  "puntos_a_negociar": ["Lista de cláusulas o aspectos que convendría renegociar antes de la firma"],
  "leyes_aplicables": ["Normativa chilena aplicable con mención de artículos clave"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo",
  "notas_adicionales": "Observaciones sobre equilibrio obligacional y vigencia"
}
PROMPT,

            default => $this->standardGenericSchema($analysisType),
        };
    }

    private function advancedSchemas(AnalysisType $analysisType): string
    {
        return match ($analysisType) {
            AnalysisType::FullSummary => <<<'PROMPT'
ESTRUCTURA DE RESPUESTA JSON (Advanced):
{
  "resumen_general": "Diagnóstico integral exhaustivo del instrumento legal en 3-4 párrafos",
  "tipo_documento": "Calificación jurídica formal del acto o contrato",
  "partes_involucradas": ["Identificación de partes, representaciones y domicilios contractuales"],
  "clausulas_principales": [
    {
      "numero": "Cláusula X",
      "titulo": "Título de la estipulación",
      "explicacion": "Desglose jurídico y operativo",
      "implicacion_legal": "Obligaciones asumidas y efectos ante incumplimiento",
      "potencial_abuso_o_asimetria": "Evaluación de reciprocidad o asimetría",
      "referencia_legal": "Artículos específicos del ordenamiento chileno"
    }
  ],
  "analisis_asimetria_contractual": {
    "nivel_desequilibrio": "alto|moderado|equilibrado",
    "parte_favorecida": "Identificación de la parte con ventaja desproporcionada",
    "clausulas_asimetricas": ["Lista de cláusulas con desbalance o renuncia unilateral"]
  },
  "vacios_legales_y_ambiguedades": [
    {
      "materia": "Tema omitido o ambiguo (ej: caso fortuito, indexación, terminación anticipada)",
      "impacto_potencial": "Riesgo en caso de controversia",
      "sugerencia_redaccion": "Qué estipulación debería agregarse o aclararse"
    }
  ],
  "riesgos_identificados": [
    {
      "riesgo": "Descripción exhaustiva del riesgo",
      "severidad": "alta|media|baja",
      "probabilidad": "alta|media|baja",
      "clausula_relacionada": "Cláusula de origen",
      "referencia_legal": "Código o ley chilena específica (ej: Art. 1544, 1545, 1546 CC)",
      "impacto_financiero_o_patrimonial": "Estimación cualitativa del daño potencial",
      "estrategia_mitigacion": "Medida preventiva o contraoferta"
    }
  ],
  "propuestas_de_contra_clausulas": [
    {
      "clausula_original": "Referencia a la cláusula actual",
      "problema": "Por qué es perjudicial para el usuario",
      "redaccion_propuesta": "Texto legal redactado listo para proponer a la contraparte"
    }
  ],
  "recomendaciones_estrategicas": [
    {
      "titulo": "Acción estratégica",
      "descripcion": "Detalle técnico",
      "prioridad": "alta|media|baja",
      "fase": "negociacion_previa|adecuacion_formal|gestion_post_firma"
    }
  ],
  "analisis_resolucion_controversias": {
    "mecanismo": "Tribunales ordinarios | Arbitraje (árbitro arbitrador / de derecho / mixto)",
    "sede_y_costos": "Evaluación de costos y accesibilidad de la cláusula de competencia",
    "evaluacion": "Favorable o desfavorable para el cliente"
  },
  "leyes_aplicables": ["Marco normativo exhaustivo con artículos"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT,

            default => $this->advancedGenericSchema($analysisType),
        };
    }

    private function premiumSchemas(AnalysisType $analysisType): string
    {
        return match ($analysisType) {
            AnalysisType::FullSummary => <<<'PROMPT'
ESTRUCTURA DE RESPUESTA JSON (Premium - Frontier Forensic Audit):
{
  "briefing_ejecutivo_gerencial": "Memorándum de nivel institucional para Gerencia General y Dirección Legal sintetizando viabilidad, contingencias mayores y postura recomendada",
  "tipo_documento": "Calificación jurídica y naturaleza dogmática del contrato",
  "partes_involucradas": ["Detalle de partes, personería y facultades requeridas"],
  "clausulas_principales": [
    {
      "numero": "Cláusula X",
      "titulo": "Título de la estipulación",
      "diagnostico_juridico": "Análisis forense de validez y eficacia bajo derecho chileno",
      "implicacion_patrimonial": "Carga económica o contingencia ligada",
      "evaluacion_validez": "plenamente_valida|anulable_relativa|nula_absoluta_objeto_ilicito|inoponible",
      "referencia_normativa": "Leyes y artículos chilenos específicos"
    }
  ],
  "redlining_clausulas_sugeridas": [
    {
      "clausula_numero": "Cláusula X",
      "texto_original_resumido": "Texto contractual actual objetable",
      "diagnostico_critico": "Vicios, desbalance o riesgos detectados",
      "clausula_sugerida": "Texto redactado formalmente en derecho chileno para sustituir la estipulación riesgosa",
      "justificacion_negociacion": "Argumento persuasivo para fundamentar el cambio ante la contraparte"
    }
  ],
  "simulacion_escenarios_adversos": {
    "escenario_incumplimiento_contraparte": {
      "mecanismos_remedio_disponibles": "Acciones judiciales disponibles (ejecutiva, resolución contractual, indemnización)",
      "tiempo_estimado_cobro_resolucion": "Estimación del tiempo en tribunales civiles o arbitrales chilenos",
      "vulnerabilidad_del_cliente": "Riesgo que asume el cliente en este escenario"
    },
    "escenario_quiebra_o_insolvencia": {
      "impacto_ley_20720": "Cómo opera la prelación de créditos y retención de bienes",
      "proteccion_actual": "insuficiente|moderada|robusta"
    },
    "escenario_fuerza_mayor_o_imprevisto": {
      "tratamiento_teoria_imprevision": "Disposiciones sobre caso fortuito y revisión contractual bajo doctrina de la CS chilena",
      "posicion_cliente": "Fuerte o desprotegida"
    }
  },
  "riesgos_identificados": [
    {
      "id": 1,
      "riesgo": "Denominación del riesgo institucional",
      "severidad": "alta|media|baja",
      "probabilidad": "alta|media|baja",
      "clausula_origen": "Cláusula relacionada",
      "referencia_legal": "Norma chilena específica",
      "tendencia_jurisprudencial_chilena": "Cómo resuelven la Corte Suprema o Cortes de Apelaciones este tipo de conflicto",
      "plan_accion_inmediato": "Paso procesal o negocial concreto"
    }
  ],
  "doctrina_y_jurisprudencia_relevante": [
    {
      "materia": "Institución jurídica en discusión (ej: Cláusula penal enorme, exclusión de dolo futuro Art 1465 CC, renuncia de acciones laborales)",
      "criterio_judicial_predominante": "Línea jurisprudencial consolidada en Chile"
    }
  ],
  "matriz_riesgo_residual": {
    "nivel_riesgo_global": "critico|alto|moderado|bajo",
    "recomendacion_firma": "proceder_con_modificaciones_criticas|renegociar_integralmente|rechazar_firma",
    "contingencia_patrimonial_maxima": "Estimación cualitativa del pasivo eventual en peor escenario"
  },
  "leyes_aplicables": ["Inventario exhaustivo de leyes, códigos y reglamentos chilenos"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT,

            default => $this->premiumGenericSchema($analysisType),
        };
    }

    // ---------------------------------------------------------------
    // Generic Schemas Fallbacks for specific analysis types by tier
    // ---------------------------------------------------------------

    private function lightweightGenericSchema(AnalysisType $analysisType): string
    {
        return <<<PROMPT
TIPO DE ANÁLISIS: {$analysisType->label()} (Lightweight)
Responde en formato JSON con:
{
  "tipo_documento": "Tipo identificado",
  "resumen_general": "Resumen conciso en 1-2 párrafos",
  "hallazgos_principales": ["Hallazgo 1", "Hallazgo 2", "Hallazgo 3"],
  "riesgos_destacados": [
    {
      "riesgo": "Descripción",
      "severidad": "alta|media|baja",
      "recomendacion": "Qué hacer"
    }
  ],
  "recomendaciones": ["Acción 1", "Acción 2"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT;
    }

    private function standardGenericSchema(AnalysisType $analysisType): string
    {
        return <<<PROMPT
TIPO DE ANÁLISIS: {$analysisType->label()} (Standard)
Responde en formato JSON estructurado con:
{
  "tipo_documento": "Tipo identificado",
  "resumen_general": "Resumen analítico en 2-3 párrafos",
  "analisis_detallado": [
    {
      "elemento": "Concepto analizado",
      "detalle": "Explicación clara",
      "referencia_legal": "Ley chilena aplicable"
    }
  ],
  "riesgos": [
    {
      "titulo": "Riesgo",
      "severidad": "alta|media|baja",
      "explicacion": "Análisis del riesgo",
      "recomendacion": "Paso a seguir"
    }
  ],
  "recomendaciones": [
    {
      "titulo": "Recomendación",
      "prioridad": "alta|media|baja",
      "justificacion": "Por qué se recomienda"
    }
  ],
  "leyes_aplicables": ["Leyes chilenas relevantes"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT;
    }

    private function advancedGenericSchema(AnalysisType $analysisType): string
    {
        return <<<PROMPT
TIPO DE ANÁLISIS: {$analysisType->label()} (Advanced)
Responde en formato JSON con análisis profundo y detallado:
{
  "tipo_documento": "Calificación jurídica",
  "resumen_analitico": "Diagnóstico integral en 3 párrafos",
  "hallazgos_profundos": [
    {
      "materia": "Materia analizada",
      "diagnostico": "Evaluación técnico-legal",
      "impacto_asimetrico": "Evaluación de desbalance obligacional",
      "referencia_legal_chilena": "Artículo y cuerpo normativo chileno"
    }
  ],
  "riesgos_estructurados": [
    {
      "riesgo": "Descripción exhaustiva",
      "severidad": "alta|media|baja",
      "probabilidad": "alta|media|baja",
      "impacto_patrimonial": "Efecto económico o legal",
      "estrategia_mitigacion": "Acción concreta sugerida"
    }
  ],
  "propuestas_correctivas": [
    {
      "punto": "Tema a subsanar",
      "texto_sugerido": "Redacción legal propuesta para negociar"
    }
  ],
  "leyes_aplicables": ["Normativa chilena exhaustiva"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT;
    }

    private function premiumGenericSchema(AnalysisType $analysisType): string
    {
        return <<<PROMPT
TIPO DE ANÁLISIS: {$analysisType->label()} (Premium Forensic)
Responde en formato JSON de máxima exhaustividad y nivel institucional:
{
  "briefing_ejecutivo": "Diagnóstico de alto nivel para Dirección Jurídica y Gerencia",
  "calificacion_juridica": "Naturaleza técnica del acto o contrato",
  "auditoria_forense": [
    {
      "seccion": "Sección analizada",
      "diagnostico_legal": "Análisis forense bajo legislación y doctrina chilena",
      "contingencia_patrimonial": "Nivel de exposición económica",
      "referencia_normativa": "Leyes y artículos específicos chilenos",
      "redlining_sugerido": "Texto alternativo propuesto para mitigar contingencia"
    }
  ],
  "simulacion_contingencias": {
    "riesgo_litigioso": "Evaluación de defensabilidad en tribunales chilenos",
    "criterio_jurisprudencial": "Doctrina predominante de la Corte Suprema chilena",
    "vulnerabilidad_contractual": "critica|alta|moderada|baja"
  },
  "riesgos_institucionales": [
    {
      "riesgo": "Denominación del riesgo",
      "severidad": "alta|media|baja",
      "probabilidad": "alta|media|baja",
      "fundamento_dogmatico": "Justificación doctrinal y legal chilena",
      "plan_accion": "Medida correctiva inmediata"
    }
  ],
  "recomendacion_estrategica_final": "Directiva concluyente para el equipo directivo",
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT;
    }
}
