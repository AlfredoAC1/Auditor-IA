<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentType: string
{
    // Contratos y acuerdos
    case Contract = 'contract';
    case Amendment = 'amendment';
    case LeaseAgreement = 'lease_agreement';
    case EmploymentContract = 'employment_contract';

    // Documentos notariales
    case NotarialDeed = 'notarial_deed';
    case PowerOfAttorney = 'power_of_attorney';
    case Affidavit = 'affidavit';

    // Documentos administrativos y financieros
    case Administrative = 'administrative';
    case Financial = 'financial';
    case Invoice = 'invoice';
    case TaxDocument = 'tax_document';

    // Otros documentos legales
    case CourtFiling = 'court_filing';
    case LegalOpinion = 'legal_opinion';
    case Other = 'other';

    /**
     * Get human-readable label for the document type.
     * Labels are in Spanish (Chile) for the target user base.
     */
    public function label(): string
    {
        return match ($this) {
            self::Contract => 'Contrato',
            self::Amendment => 'Adenda / Modificación',
            self::LeaseAgreement => 'Contrato de Arriendo',
            self::EmploymentContract => 'Contrato de Trabajo',
            self::NotarialDeed => 'Escritura Notarial',
            self::PowerOfAttorney => 'Poder Notarial',
            self::Affidavit => 'Declaración Jurada',
            self::Administrative => 'Documento Administrativo',
            self::Financial => 'Documento Financiero',
            self::Invoice => 'Factura / Boleta',
            self::TaxDocument => 'Documento Tributario',
            self::CourtFiling => 'Escrito Judicial',
            self::LegalOpinion => 'Opinión Legal',
            self::Other => 'Otro',
        };
    }

    /**
     * Get a brief description of what this document type covers.
     * Helps non-specialized users select the correct type.
     */
    public function description(): string
    {
        return match ($this) {
            self::Contract => 'Contratos de compraventa, prestación de servicios u otros acuerdos',
            self::Amendment => 'Modificaciones o adendas a contratos existentes',
            self::LeaseAgreement => 'Contratos de arriendo de inmuebles o bienes',
            self::EmploymentContract => 'Contratos laborales, finiquitos, anexos de empleo',
            self::NotarialDeed => 'Escrituras públicas notariales',
            self::PowerOfAttorney => 'Mandatos o poderes otorgados ante notario',
            self::Affidavit => 'Declaraciones juradas ante notario o tribunal',
            self::Administrative => 'Resoluciones, oficios o documentos de organismos públicos',
            self::Financial => 'Estados financieros, balances, reportes contables',
            self::Invoice => 'Facturas electrónicas, boletas de honorarios',
            self::TaxDocument => 'Declaraciones de impuestos, formularios SII',
            self::CourtFiling => 'Demandas, contestaciones, recursos judiciales',
            self::LegalOpinion => 'Informes en derecho u opiniones jurídicas',
            self::Other => 'Otro tipo de documento no clasificado',
        };
    }
}
