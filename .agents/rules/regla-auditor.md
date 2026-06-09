---
trigger: always_on
---

Estamos desarrollando un SaaS de auditorías legales en Laravel 12.

Contexto del sistema:
Arquitectura multi-tenant
PostgreSQL
Laravel 12
PHP 8.3+
Docker
Redis + Queues
Laravel Sanctum
OCR + IA documental
Integración futura con embeddings y RAG
Los documentos legales son sensibles y requieren trazabilidad

Objetivo actual:
Implementar el flujo inicial de subida y procesamiento de documentos legales.

Quiero que diseñes una arquitectura limpia y escalable para:

Un controller encargado de:
recibir archivos PDF/DOCX
validar archivos
asociarlos al tenant autenticado
almacenar metadata
guardar el archivo en storage privado
despachar procesamiento asincrónico

Un pipeline posterior que:
procese el documento
extraiga texto
envíe chunks al modelo Groq
permita futuras integraciones con embeddings y auditoría IA

Requisitos técnicos:
NO colocar lógica de negocio en controllers
Usar Form Requests
Usar Services/Actions
Usar Jobs para procesamiento pesado
Diseñar pensando en escalabilidad
Todo debe ser multi-tenant safe
Toda acción debe ser auditable
Código production-ready
Explicar arquitectura antes de escribir código
Generar estructura de carpetas sugerida
Explicar riesgos de seguridad
Explicar cómo evitar problemas futuros de performance

Quiero comenzar únicamente con:
migración
modelo
form request
controller
service
job
flujo de upload

NO implementar aún OCR ni embeddings.
NO implementar frontend todavía.
NO usar código simplificado tipo tutorial.

Nunca generar:
lógica compleja en controllers
archivos innecesarios
helpers globales
código sin tipado
queries sin tenant scope
procesamiento síncrono de PDFs

Siempre:
usar dependency injection
usar DTOs si el flujo crece
usar enums para estados
usar policies
usar logs estructurados

