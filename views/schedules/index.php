<?php
/**
 * Vista: Lista de Horarios
 * 
 * CLIENTE: Esta vista se renderiza en el navegador del usuario.
 * Muestra los datos que el SERVIDOR envió después de procesar la petición.
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Gestión de Horarios</h2>

<!-- CLIENTE: Botón que envía petición GET al SERVIDOR -->
<div class="actions">
    <a href="/index.php?controller=schedule&action=create" class="btn btn-primary">➕ Nuevo Horario</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php 
        switch($_GET['success']) {
            case 'created': echo 'Horario creado exitosamente'; break;
            case 'updated': echo 'Horario actualizado exitosamente'; break;
            case 'deleted': echo 'Horario eliminado exitosamente'; break;
        }
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php 
        switch($_GET['error']) {
            case 'not_found': echo 'Horario no encontrado'; break;
            case 'delete_failed': echo 'Error al eliminar el horario'; break;
            default: echo 'Error desconocido'; break;
        }
        ?>
    </div>
<?php endif; ?>

<!-- CLIENTE: Tabla que muestra datos recibidos del SERVIDOR -->
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Día de la Semana</th>
            <th>Hora de Apertura</th>
            <th>Hora de Cierre</th>
            <th>Estado</th>
            <th>Notas</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($schedules)): ?>
            <tr>
                <td colspan="7" class="text-center">No hay horarios registrados</td>
            </tr>
        <?php else: ?>
            <?php foreach ($schedules as $schedule): ?>
                <tr>
                    <td><?php echo htmlspecialchars($schedule['id']); ?></td>
                    <td><strong><?php echo htmlspecialchars($schedule['day_of_week']); ?></strong></td>
                    <td><?php echo date('h:i A', strtotime($schedule['opening_time'])); ?></td>
                    <td><?php echo date('h:i A', strtotime($schedule['closing_time'])); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $schedule['is_active'] ? 'success' : 'secondary'; ?>">
                            <?php echo $schedule['is_active'] ? 'Activo' : 'Inactivo'; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($schedule['notes'] ?? '-'); ?></td>
                    <td class="actions-cell">
                        <!-- CLIENTE: Enlaces que envían peticiones al SERVIDOR -->
                        <a href="/index.php?controller=schedule&action=edit&id=<?php echo $schedule['id']; ?>" class="btn btn-sm btn-secondary">Editar</a>
                        <a href="/index.php?controller=schedule&action=delete&id=<?php echo $schedule['id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('¿Está seguro de eliminar este horario?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

