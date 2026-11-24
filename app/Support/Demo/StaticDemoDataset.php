<?php

declare(strict_types=1);

namespace App\Support\Demo;

use App\Enums\AnalysisType;

/**
 * In-memory static dataset for the Vercel demonstrative environment.
 *
 * Keeps zero dependencies on Eloquent, database connections, or external APIs.
 * The structure mirrors Document, DocumentAnalysis, and AiCreditLedger payloads
 * so it can also be reused later by a database seeder for Supabase/PostgreSQL.
 */
final class StaticDemoDataset
{
    /**
     * @return array{name: string, email: string, initials: string, organization_name: string, plan_slug: string, plan_name: string}
     */
    public static function user(): array
    {
        return [
            'name' => 'Constanza Silva',
            'email' => 'demo-pro@auditor-ia.cl',
            'initials' => 'CS',
            'organization_name' => 'Estudio Jurídico Silva & Asociados (Demo)',
            'plan_slug' => 'pro',
            'plan_name' => 'Pro',
        ];
    }

    /**
     * @return array{
     *     creditsRemaining: int,
     *     creditsQuota: int,
     *     creditRefreshInterval: string,
     *     daysUntilRefresh: int,
     *     planSlug: string,
     *     planName: string,
     *     allowsModelSelection: bool,
     * }
     */
    public static function credits(): array
    {
        return [
            'creditsRemaining' => 842,
            'creditsQuota' => 1000,
            'creditRefreshInterval' => 'monthly',
            'daysUntilRefresh' => 18,
            'planSlug' => 'pro',
            'planName' => 'Pro',
            'allowsModelSelection' => true,
        ];
    }

    /**
     * @return array<int, array{
     *     slug: string,
     *     display_name: string,
     *     provider_label: string,
     *     provider_key: string,
     *     tier_label: string,
     *     multiplier: string,
     *     badge_text: ?string,
     *     description: string,
     *     footer_note: string,
     *     accent: string,
     * }>
     */
    public static function aiModels(): array
    {
        return [
            [
                'slug' => 'cerebras-gemma4',
                'display_name' => 'Gemma 4 31B',
                'provider_label' => 'Cerebras AI',
                'provider_key' => 'cerebras',
                'tier_label' => 'Standard (2x)',
                'multiplier' => '2.0x',
                'badge_text' => '40k Tokens',
                'description' => 'Capacidad de hasta 40.000 tokens de salida para análisis legal exhaustivo y cláusulas detalladas.',
                'footer_note' => 'Inferencia ultra veloz',
                'accent' => 'amber',
            ],
            [
                'slug' => 'llama-70b-versatile',
                'display_name' => 'Llama 3.3 70B',
                'provider_label' => 'Groq Cloud',
                'provider_key' => 'groq',
                'tier_label' => 'Standard (2x)',
                'multiplier' => '2.0x',
                'badge_text' => null,
                'description' => 'Razonamiento jurídico equilibrado y alta precisión en detección de riesgos legales.',
                'footer_note' => 'Respuesta instantánea',
                'accent' => 'icy',
            ],
            [
                'slug' => 'nvidia-llama-70b',
                'display_name' => 'Llama 70B (NIM)',
                'provider_label' => 'NVIDIA NIM',
                'provider_key' => 'nvidia_nim',
                'tier_label' => 'Avanzado (3x)',
                'multiplier' => '3.0x',
                'badge_text' => null,
                'description' => 'Inferencia corporativa en infraestructura NVIDIA para auditorías críticas.',
                'footer_note' => 'Fidelidad máxima',
                'accent' => 'emerald',
            ],
            [
                'slug' => 'llama-8b-instant',
                'display_name' => 'Llama 3.1 8B',
                'provider_label' => 'Groq Cloud',
                'provider_key' => 'groq',
                'tier_label' => 'Ligero (1x)',
                'multiplier' => '1.0x',
                'badge_text' => null,
                'description' => 'Ideal para revisiones preliminares y resúmenes ejecutivos económicos.',
                'footer_note' => '1 Crédito / análisis',
                'accent' => 'icy-muted',
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    public static function analysisTypeCounts(): array
    {
        $counts = [
            AnalysisType::Recommendation->value => 4,
            AnalysisType::Explanation->value => 3,
            AnalysisType::ClauseAnalysis->value => 6,
            AnalysisType::RiskAssessment->value => 5,
            AnalysisType::FullSummary->value => 8,
            AnalysisType::ContextualSummary->value => 2,
        ];

        $result = [];
        foreach (AnalysisType::cases() as $type) {
            $result[$type->value] = $counts[$type->value] ?? 0;
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function documents(): array
    {
        return [
            [
                'id' => 'demo-doc-1',
                'slug' => 'contrato-arriendo-comercial',
                'original_filename' => 'Contrato_Arriendo_Comercial_Providencia_2026.pdf',
                'mime_type' => 'application/pdf',
                'file_size_bytes' => 1489920,
                'status' => 'processed',
                'created_at_human' => 'hace 2 días',
                'audited_at_human' => 'hace 2 días',
                'analysis' => [
                    'analysis_type' => AnalysisType::FullSummary->value,
                    'llm_provider' => 'cerebras',
                    'llm_model' => 'gemma-4-31b-it',
                    'processing_time_ms' => 14200,
                    'input_tokens' => 4820,
                    'output_tokens' => 2190,
                    'content' => [
                        'tipo_documento' => 'Contrato de Arrendamiento Comercial',
                        'requiere_abogado' => true,
                        'resumen_general' => 'Contrato de arrendamiento de inmueble comercial ubicado en Providencia, Santiago, con una vigencia forzosa inicial de 36 meses y renta mensual pactada en UF 68 más IVA. Se identifican cláusulas de reajuste unilateral, término anticipado asimétrico en favor del arrendador y renuncia amplia a derechos de retención e indemnización por mejoras necesarias, por lo que requiere revisión previa antes de la firma.',
                        'partes_involucradas' => [
                            'Inmobiliaria Los Leones SpA (Arrendador)',
                            'Consultora Digital Pacífico Ltda. (Arrendatario)',
                            'Constanza Silva Morales (Aval y Codeudora Solidaria)',
                        ],
                        'leyes_aplicables' => [
                            'Ley N° 18.101 sobre Arrendamiento de Predios Urbanos',
                            'Código Civil de Chile (Arts. 1915 y ss.)',
                            'Ley N° 19.496 sobre Protección de los Derechos de los Consumidores',
                            'Ley N° 18.010 sobre Operaciones de Crédito de Dinero',
                        ],
                        'riesgos_identificados' => [
                            [
                                'riesgo' => 'Cláusula de término anticipado unilateral sin indemnización',
                                'severidad' => 'alta',
                                'explicacion' => 'La cláusula décimo segunda faculta al arrendador para poner término inmediato al contrato ante cualquier atraso superior a 5 días hábiles, manteniendo vigente el cobro de las rentas futuras como multa penal.',
                                'referencia_legal' => 'Ley N° 18.101 Art. 10 y Código Civil Art. 1544 (Cláusula Penal Enorme)',
                            ],
                            [
                                'riesgo' => 'Renuncia anticipada a reembolso de mejoras necesarias',
                                'severidad' => 'media',
                                'explicacion' => 'El arrendatario renuncia expresamente a exigir reembolso o derecho legal de retención incluso respecto de reparaciones estructurales o sanitarias urgentes indispensables para operar.',
                                'referencia_legal' => 'Código Civil de Chile Arts. 1927, 1935 y 1937',
                            ],
                            [
                                'riesgo' => 'Multa diaria acumulativa por retardo en restitución',
                                'severidad' => 'media',
                                'explicacion' => 'Se establece un recargo equivalente al 150% de la renta diaria proporcional más interés máximo convencional sin tope de devengo durante la tramitación judicial.',
                                'referencia_legal' => 'Ley N° 18.010 Art. 6 y Ley N° 18.101 Art. 6',
                            ],
                        ],
                        'clausulas_principales' => [
                            [
                                'numero' => '01',
                                'titulo' => 'Objeto y Destino Exclusivo del Inmueble',
                                'explicacion' => 'Autoriza únicamente actividades de oficina administrativa y servicios profesionales. Prohíbe subarrendar o ceder el contrato sin autorización escrita previa del arrendador.',
                                'implicacion_legal' => 'Cualquier cambio de giro comercial o subarriendo parcial sin anexo firmado constituye causal de término inmediato.',
                            ],
                            [
                                'numero' => '02',
                                'titulo' => 'Renta, Garantía y Reajuste en UF',
                                'explicacion' => 'Fija una renta mensual de UF 68 pagadera dentro de los primeros 5 días corridos de cada mes, más dos meses de garantía sin devengo de intereses.',
                                'implicacion_legal' => 'La garantía no puede imputarse al pago del último mes de renta salvo acuerdo expreso posterior.',
                            ],
                            [
                                'numero' => '03',
                                'titulo' => 'Plazo Forzoso de 36 Meses y Renovación Tácita',
                                'explicacion' => 'El arrendatario queda obligado durante todo el primer trienio; si desea salir anticipadamente, debe pagar el 50% de las rentas remanentes.',
                                'implicacion_legal' => 'Restringe severamente la flexibilidad financiera ante cambios operativos de la empresa.',
                            ],
                            [
                                'numero' => '04',
                                'titulo' => 'Mantención, Mejoras y Seguros Obligatorios',
                                'explicacion' => 'Traspasa al arrendatario la obligación de contratar seguro contra incendio y responsabilidad civil, dejando a beneficio del inmueble toda mejora adherida.',
                                'implicacion_legal' => 'Se sugiere excluir las reparaciones estructurales y de techumbre conforme al Art. 1927 del Código Civil.',
                            ],
                        ],
                        'recomendaciones' => [
                            [
                                'recomendacion' => 'Limitar la cláusula penal por término anticipado',
                                'prioridad' => 'alta',
                                'justificacion' => 'Acotar la multa a un máximo de 2 meses de renta para evitar acumulación desproporcionada de rentas futuras no devengadas.',
                            ],
                            [
                                'recomendacion' => 'Diferenciar mejoras útiles de reparaciones necesarias',
                                'prioridad' => 'media',
                                'justificacion' => 'Establecer que las fallas estructurales, eléctricas de matriz o sanitarias previas sean de cargo del arrendador dentro de un plazo de 10 días.',
                            ],
                            [
                                'recomendacion' => 'Acotar responsabilidad del codeudor solidario',
                                'prioridad' => 'media',
                                'justificacion' => 'Limitar la fianza solidaria exclusivamente al período inicial de 36 meses, evitando su prórroga automática indefinida.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'demo-doc-2',
                'slug' => 'nda-confidencialidad-tech',
                'original_filename' => 'NDA_Confidencialidad_Software_Chile.docx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_size_bytes' => 491520,
                'status' => 'processed',
                'created_at_human' => 'hace 5 días',
                'audited_at_human' => 'hace 5 días',
                'analysis' => [
                    'analysis_type' => AnalysisType::ClauseAnalysis->value,
                    'llm_provider' => 'groq',
                    'llm_model' => 'llama-3.3-70b-versatile',
                    'processing_time_ms' => 8600,
                    'input_tokens' => 2450,
                    'output_tokens' => 1480,
                    'content' => [
                        'tipo_documento' => 'Acuerdo de Confidencialidad (NDA Bilateral)',
                        'requiere_abogado' => false,
                        'resumen_general' => 'Acuerdo bilateral de confidencialidad y no divulgación destinado a proteger código fuente, arquitectura cloud y datos comerciales durante una negociación tecnológica. El equilibrio general entre las partes es adecuado, aunque presenta una definición excesivamente amplia de información confidencial perpetua y una cláusula de no competencia indirecta que conviene precisar.',
                        'partes_involucradas' => [
                            'Andes Cloud Ingeniería SpA',
                            'Estudio Jurídico Silva & Asociados',
                        ],
                        'leyes_aplicables' => [
                            'Ley N° 19.039 sobre Propiedad Industrial (Secretos Empresariales)',
                            'Ley N° 19.628 sobre Protección de la Vida Privada',
                            'Ley N° 17.336 sobre Propiedad Intelectual',
                        ],
                        'riesgos_identificados' => [
                            [
                                'riesgo' => 'Vigencia perpetua para información técnica general',
                                'severidad' => 'media',
                                'explicacion' => 'El acuerdo extiende la obligación de reserva de manera indefinida para toda información intercambiada, sin distinguir secretos empresariales de información técnica estándar.',
                                'referencia_legal' => 'Ley N° 19.039 Art. 86 (Definición de Secreto Empresarial)',
                            ],
                            [
                                'riesgo' => 'Multa fija en UF sin prueba de perjuicios efectivos',
                                'severidad' => 'baja',
                                'explicacion' => 'Contempla una cláusula penal de UF 500 por incumplimiento, acumulable con indemnización ordinaria si se acredita daño mayor.',
                                'referencia_legal' => 'Código Civil de Chile Art. 1535 y Art. 1543',
                            ],
                        ],
                        'clausulas_principales' => [
                            [
                                'numero' => '01',
                                'titulo' => 'Definición de Información Confidencial y Exclusiones',
                                'explicacion' => 'Protege documentación técnica, APIs, bases de datos y propuestas comerciales, excluyendo información de dominio público o desarrollada independientemente.',
                                'implicacion_legal' => 'Las exclusiones estándar protegen adecuadamente frente a reclamos sobre conocimiento previo.',
                            ],
                            [
                                'numero' => '02',
                                'titulo' => 'Tratamiento de Datos Personales',
                                'explicacion' => 'Obliga a aplicar medidas de seguridad razonables cuando los archivos compartidos contengan datos personales de clientes o trabajadores.',
                                'implicacion_legal' => 'Alineado con deberes de reserva y finalidad establecidos en la Ley N° 19.628.',
                            ],
                            [
                                'numero' => '03',
                                'titulo' => 'Devolución y Destrucción Certificada',
                                'explicacion' => 'Exige eliminar o devolver copias digitales dentro de 15 días hábiles tras el término de las conversaciones.',
                                'implicacion_legal' => 'Conviene exceptuar copias automáticas de respaldo cifradas mientras cumplan su ciclo de retención.',
                            ],
                        ],
                        'recomendaciones' => [
                            [
                                'recomendacion' => 'Acotar vigencia general a 3 años tras el término',
                                'prioridad' => 'media',
                                'justificacion' => 'Mantener la reserva perpetua solo respecto de secretos empresariales estrictos y datos personales.',
                            ],
                            [
                                'recomendacion' => 'Agregar excepción para backups automatizados',
                                'prioridad' => 'baja',
                                'justificacion' => 'Evita incumplimientos técnicos involuntarios derivados de políticas de respaldo cloud.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'demo-doc-3',
                'slug' => 'prestacion-servicios-ti',
                'original_filename' => 'Contrato_Prestacion_Servicios_Desarrollo_SaaS.pdf',
                'mime_type' => 'application/pdf',
                'file_size_bytes' => 936960,
                'status' => 'processed',
                'created_at_human' => 'hace 1 semana',
                'audited_at_human' => 'hace 1 semana',
                'analysis' => [
                    'analysis_type' => AnalysisType::RiskAssessment->value,
                    'llm_provider' => 'nvidia_nim',
                    'llm_model' => 'meta/llama-3.3-70b-instruct',
                    'processing_time_ms' => 11400,
                    'input_tokens' => 3610,
                    'output_tokens' => 1820,
                    'content' => [
                        'tipo_documento' => 'Contrato de Prestación de Servicios Tecnológicos',
                        'requiere_abogado' => true,
                        'resumen_general' => 'Contrato de desarrollo de plataforma web a medida bajo modalidad de hitos mensuales. Destaca positivamente la cesión clara de propiedad intelectual contra pago efectivo de cada hito, pero se advierte responsabilidad ilimitada del proveedor ante caídas de terceros y ausencia de procedimiento formal de aprobación tácita de entregables.',
                        'partes_involucradas' => [
                            'FinTech Austral S.A. (Mandante)',
                            'DevLegal Soluciones SpA (Prestador)',
                        ],
                        'leyes_aplicables' => [
                            'Código Civil de Chile (Arts. 1545 y 2006)',
                            'Ley N° 17.336 sobre Propiedad Intelectual',
                            'Ley N° 21.131 sobre Pago a 30 Días',
                        ],
                        'riesgos_identificados' => [
                            [
                                'riesgo' => 'Ausencia de tope de responsabilidad (Liability Cap)',
                                'severidad' => 'alta',
                                'explicacion' => 'El prestador responde sin límite pecuniario por lucro cesante o fallas de disponibilidad atribuibles a proveedores cloud externos.',
                                'referencia_legal' => 'Código Civil Art. 1558 (Previsibilidad de los perjuicios)',
                            ],
                            [
                                'riesgo' => 'Plazo indefinido de aceptación de entregables',
                                'severidad' => 'media',
                                'explicacion' => 'El pago de cada factura depende de la aprobación expresa del mandante, sin contemplar silencio positivo tras un plazo de revisión.',
                                'referencia_legal' => 'Ley N° 21.131 / Ley N° 19.983 sobre Mérito Ejecutivo de Facturas',
                            ],
                        ],
                        'clausulas_principales' => [
                            [
                                'numero' => '01',
                                'titulo' => 'Propiedad Intelectual sobre Código Fuente',
                                'explicacion' => 'Transfiere los derechos patrimoniales del software desarrollado al mandante sujeto al pago íntegro del hito respectivo.',
                                'implicacion_legal' => 'Condicionar la cesión al pago protege al prestador frente a morosidad.',
                            ],
                            [
                                'numero' => '02',
                                'titulo' => 'Acuerdos de Nivel de Servicio (SLA 99,5%)',
                                'explicacion' => 'Exige disponibilidad continua con descuentos escalonados por tiempo fuera de línea.',
                                'implicacion_legal' => 'Se debe excluir expresamente ventanas de mantenimiento programado y fuerza mayor.',
                            ],
                            [
                                'numero' => '03',
                                'titulo' => 'Facturación y Pago a 30 Días',
                                'explicacion' => 'Establece emisión de factura electrónica una vez validado el hito en repositorio.',
                                'implicacion_legal' => 'Sujeto a plazos imperativos de la Ley N° 21.131 en Chile.',
                            ],
                        ],
                        'recomendaciones' => [
                            [
                                'recomendacion' => 'Incorporar tope de responsabilidad contractual',
                                'prioridad' => 'alta',
                                'justificacion' => 'Fijar límite equivalente al total de honorarios pagados en los últimos 6 meses, excluyendo dolo o culpa grave.',
                            ],
                            [
                                'recomendacion' => 'Definir aprobación tácita a los 10 días hábiles',
                                'prioridad' => 'media',
                                'justificacion' => 'Si el mandante no formula observaciones fundadas dentro de 10 días hábiles, el entregable se entiende aprobado.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findDocument(string $slug): ?array
    {
        foreach (self::documents() as $document) {
            if ($document['slug'] === $slug) {
                return $document;
            }
        }

        return null;
    }

    /**
     * @return array{analyzedCount: int, totalDocuments: int, riskCount: int, avgTime: string}
     */
    public static function metrics(): array
    {
        $documents = self::documents();
        $riskCount = 0;
        $totalTimeMs = 0;

        foreach ($documents as $doc) {
            $risks = $doc['analysis']['content']['riesgos_identificados'] ?? [];
            $riskCount += is_array($risks) ? count($risks) : 0;
            $totalTimeMs += (int) ($doc['analysis']['processing_time_ms'] ?? 0);
        }

        $count = count($documents);

        return [
            'analyzedCount' => $count,
            'totalDocuments' => $count,
            'riskCount' => $riskCount,
            'avgTime' => $count > 0 ? number_format(($totalTimeMs / $count) / 1000, 1) : '--',
        ];
    }
}
