<?php

declare(strict_types=1);

namespace App\Enums;

enum AnalysisType: string
{
    case Recommendation = 'recommendation';
    case Explanation = 'explanation';
    case ClauseAnalysis = 'clause_analysis';
    case RiskAssessment = 'risk_assessment';
    case FullSummary = 'full_summary';
    case ContextualSummary = 'contextual_summary';

    /**
     * Get human-readable label for the analysis type.
     * In Spanish for Chilean end-users.
     */
    public function label(): string
    {
        return match ($this) {
            self::Recommendation => 'Recomendaciones',
            self::Explanation => 'Explicación Simple',
            self::ClauseAnalysis => 'Análisis de Cláusulas',
            self::RiskAssessment => 'Evaluación de Riesgos',
            self::FullSummary => 'Resumen Completo',
            self::ContextualSummary => 'Análisis Contextual con Documentos de Apoyo',
        };
    }

    /**
     * Describe what this analysis type provides.
     * Helps non-specialized users choose the right analysis.
     */
    public function description(): string
    {
        return match ($this) {
            self::Recommendation => 'Puntos clave que debería revisar con un abogado',
            self::Explanation => 'Explicación del documento en lenguaje simple y claro',
            self::ClauseAnalysis => 'Desglose detallado de cada cláusula del documento',
            self::RiskAssessment => 'Identificación de riesgos y puntos de atención',
            self::FullSummary => 'Resumen completo incluyendo recomendaciones, explicaciones y riesgos',
            self::ContextualSummary => 'Análisis enriquecido que incorpora documentos de apoyo y el análisis previo como contexto',
        };
    }
}
