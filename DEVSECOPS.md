# 🔒 DevSecOps - Pipeline CI/CD

Este documento describe el pipeline de DevSecOps implementado con GitHub Actions para el proyecto.

## 📋 Overview

El pipeline de DevSecOps incluye:
- ✅ Análisis de calidad de código
- ✅ Análisis de seguridad
- ✅ Pruebas de base de datos
- ✅ Validación de estructura
- ✅ Generación de reportes

## 🔄 Flujo del Pipeline

```
┌─────────────────┐
│   Push/PR       │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  1. Code Quality & Linting          │
│     - Verificación de sintaxis      │
│     - PHP_CodeSniffer (PSR12)       │
│     - PHPStan (análisis estático)   │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  2. Security Analysis               │
│     - Búsqueda de vulnerabilidades  │
│     - Verificación de funciones     │
│       peligrosas                    │
│     - Validación de prepared        │
│       statements                    │
│     - Verificación de sanitización  │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  3. Database Tests                  │
│     - Creación de BD de prueba      │
│     - Ejecución de schema.sql        │
│     - Validación de tablas          │
│     - Verificación de estructura    │
└────────┬────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│  4. Build & Validation             │
│     - Validación de estructura      │
│     - Generación de reportes        │
│     - Creación de artifacts         │
└─────────────────────────────────────┘
```

## 📁 Archivos del Pipeline

### Workflows

1. **`.github/workflows/ci-cd.yml`**
   - Pipeline principal de CI/CD
   - Ejecuta análisis de código, seguridad y pruebas
   - Se ejecuta en push y pull requests

2. **`.github/workflows/security.yml`**
   - Análisis profundo de seguridad
   - Búsqueda de secretos expuestos
   - Análisis de dependencias
   - Se ejecuta diariamente y en push/PR

3. **`.github/workflows/deploy.yml`**
   - Pipeline de despliegue
   - Crea paquete de deploy
   - Solo se ejecuta en la rama `main`

### Configuración

- **`composer.json`**: Dependencias y scripts
- **`.phpcs.xml`**: Configuración de PHP_CodeSniffer
- **`phpstan.neon`**: Configuración de PHPStan

## 🚀 Ejecución Local

### Instalar herramientas de desarrollo

```bash
composer install
```

### Ejecutar análisis de código

```bash
# Linting con PHP_CodeSniffer
composer lint

# Análisis estático con PHPStan
composer analyse

# Verificar sintaxis PHP
find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \;
```

### Ejecutar pruebas

```bash
# Si tienes PHPUnit configurado
composer test
```

## 🔍 Jobs del Pipeline

### 1. Code Quality & Linting

- **Verificación de sintaxis PHP**: Valida que todos los archivos PHP tengan sintaxis correcta
- **PHP_CodeSniffer**: Verifica el cumplimiento del estándar PSR12
- **PHPStan**: Análisis estático de código para detectar errores potenciales

### 2. Security Analysis

- **Búsqueda de funciones peligrosas**: Detecta uso de `eval`, `exec`, `system`, etc.
- **Verificación de prepared statements**: Asegura que todas las consultas usen prepared statements
- **Verificación de sanitización**: Valida que las salidas usen `htmlspecialchars()`
- **Análisis de dependencias**: Busca vulnerabilidades conocidas

### 3. Database Tests

- **Creación de BD de prueba**: Usa PostgreSQL en Docker
- **Ejecución de schema**: Valida que el script SQL funcione correctamente
- **Verificación de tablas**: Confirma que todas las tablas se crearon
- **Validación de estructura**: Verifica la estructura de las tablas

### 4. Build & Validation

- **Validación de estructura**: Verifica que todas las carpetas necesarias existan
- **Generación de reportes**: Crea reportes del build
- **Artifacts**: Guarda reportes para revisión posterior

## 🔒 Seguridad

### Verificaciones Automáticas

1. **Prepared Statements**: Todas las consultas deben usar prepared statements
2. **Sanitización**: Todas las salidas deben estar sanitizadas
3. **Funciones Peligrosas**: No se permiten funciones como `eval()`, `exec()`, etc.
4. **Secretos**: Búsqueda automática de secretos expuestos en el código
5. **Dependencias**: Análisis de vulnerabilidades en dependencias

### Mejores Prácticas Implementadas

- ✅ Uso de prepared statements (previene SQL injection)
- ✅ Sanitización de salidas (previene XSS)
- ✅ Validación en servidor (seguridad)
- ✅ Manejo de errores apropiado
- ✅ Sin credenciales hardcodeadas

## 📊 Reportes

Los reportes se generan automáticamente y están disponibles en:

1. **GitHub Actions**: Ver los resultados de cada workflow
2. **Artifacts**: Descargar reportes detallados
3. **Security Report**: Reporte específico de seguridad

## 🛠️ Configuración de GitHub

### Secrets Requeridos (Opcional)

Si quieres usar análisis avanzados, configura estos secrets en GitHub:

- `SNYK_TOKEN`: Token de Snyk para análisis de vulnerabilidades

### Configuración de Branch Protection

Recomendado configurar branch protection en GitHub:

1. Ve a Settings → Branches
2. Agrega regla para `main` y `develop`
3. Requiere que los checks pasen antes de merge
4. Requiere revisión de código

## 📈 Métricas

El pipeline genera métricas sobre:

- Cobertura de código (si PHPUnit está configurado)
- Calidad de código (PHPStan)
- Cumplimiento de estándares (PHP_CodeSniffer)
- Vulnerabilidades encontradas
- Tiempo de ejecución del pipeline

## 🔄 Integración Continua

### Triggers

El pipeline se ejecuta automáticamente en:

- **Push** a `main` o `develop`
- **Pull Requests** hacia `main` o `develop`
- **Manual** (workflow_dispatch)
- **Programado** (security scan diario)

### Notificaciones

- Los resultados se muestran en GitHub Actions
- Los errores bloquean el merge si está configurado
- Los reportes están disponibles como artifacts

## 🎓 Para Estudiantes

Este pipeline demuestra:

1. **CI/CD**: Integración y despliegue continuo
2. **DevSecOps**: Seguridad integrada en el desarrollo
3. **Automatización**: Reducción de errores manuales
4. **Calidad**: Mantenimiento de estándares de código
5. **Testing**: Validación automática de cambios

## 📝 Notas

- Los jobs con `continue-on-error: true` no bloquean el pipeline
- El análisis de seguridad se ejecuta diariamente
- Los reportes se mantienen por 7 días en artifacts
- El deploy solo se ejecuta en la rama `main`

## 🔗 Recursos

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [PHPStan](https://phpstan.org/)
- [DevSecOps Best Practices](https://www.devsecops.org/)

---

**Pipeline configurado y listo para usar** 🚀

