<?php
/**
 * Punto de Entrada Principal - Router
 * 
 * Este archivo actúa como el SERVIDOR que recibe todas las peticiones HTTP del CLIENTE.
 * 
 * Flujo Cliente-Servidor:
 * 1. CLIENTE (navegador) → Envía petición HTTP a este archivo
 * 2. SERVIDOR (este archivo) → Interpreta la URL y delega al controlador apropiado
 * 3. CONTROLADOR → Procesa la petición, accede al MODELO
 * 4. MODELO → Consulta/actualiza la base de datos
 * 5. CONTROLADOR → Genera respuesta HTML usando la VISTA
 * 6. SERVIDOR → Envía respuesta HTML al CLIENTE
 * 7. CLIENTE → Renderiza la respuesta en el navegador
 */

// Configuración de rutas base
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Autocarga de clases
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/models/' . $class . '.php',
        BASE_PATH . '/controllers/' . $class . '.php',
        BASE_PATH . '/config/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
});

// SERVIDOR: Obtiene parámetros de la petición HTTP del CLIENTE
$controllerName = $_GET['controller'] ?? 'member';
$action = $_GET['action'] ?? 'index';

// SERVIDOR: Mapea nombres de controladores a clases
$controllers = [
    'member' => 'MemberController',
    'class' => 'ClassController',
    'payment' => 'PaymentController',
    'schedule' => 'ScheduleController'
];

// SERVIDOR: Valida que el controlador existe
if (!isset($controllers[$controllerName])) {
    die('Controlador no encontrado');
}

$controllerClass = $controllers[$controllerName];

// SERVIDOR: Valida que la clase del controlador existe
if (!class_exists($controllerClass)) {
    die('Clase del controlador no encontrada: ' . $controllerClass);
}

// SERVIDOR: Crea instancia del controlador y ejecuta la acción
try {
    $controller = new $controllerClass();
    
    // SERVIDOR: Valida que el método existe
    if (!method_exists($controller, $action)) {
        die('Acción no encontrada: ' . $action);
    }
    
    // SERVIDOR: Ejecuta la acción del controlador
    // La acción puede:
    // - Acceder a modelos (base de datos)
    // - Generar vistas (HTML para el CLIENTE)
    // - Redirigir al CLIENTE a otra página
    $controller->$action();
    
} catch (Exception $e) {
    // SERVIDOR: Maneja errores y envía respuesta al CLIENTE
    die('Error del servidor: ' . $e->getMessage());
}

