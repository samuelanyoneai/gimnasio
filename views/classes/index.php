<?php
/**
 * Vista: Lista de Clases
 * 
 * CLIENTE: Muestra las clases recibidas del SERVIDOR
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Gestión de Clases</h2>

<div class="actions">
    <a href="/index.php?controller=class&action=create" class="btn btn-primary">➕ Nueva Clase</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Instructor</th>
            <th>Horario</th>
            <th>Días</th>
            <th>Capacidad</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($classes)): ?>
            <tr>
                <td colspan="7" class="text-center">No hay clases registradas</td>
            </tr>
        <?php else: ?>
            <?php foreach ($classes as $class): ?>
                <tr>
                    <td><?php echo htmlspecialchars($class['id']); ?></td>
                    <td><?php echo htmlspecialchars($class['name']); ?></td>
                    <td><?php echo htmlspecialchars($class['instructor']); ?></td>
                    <td><?php echo htmlspecialchars(date('H:i', strtotime($class['schedule_time']))); ?></td>
                    <td><?php echo htmlspecialchars($class['schedule_days']); ?></td>
                    <td><?php echo htmlspecialchars($class['capacity']); ?></td>
                    <td class="actions-cell">
                        <a href="/index.php?controller=class&action=edit&id=<?php echo $class['id']; ?>" class="btn btn-sm btn-secondary">Editar</a>
                        <a href="/index.php?controller=class&action=delete&id=<?php echo $class['id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('¿Está seguro de eliminar esta clase?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

