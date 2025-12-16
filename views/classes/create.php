<?php
/**
 * Vista: Crear Clase
 * 
 * CLIENTE: Formulario para crear nueva clase
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Nueva Clase</h2>

<form method="POST" action="/index.php?controller=class&action=store" class="form">
    <div class="form-group">
        <label for="name">Nombre de la Clase *</label>
        <input type="text" id="name" name="name" required 
               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
               class="<?php echo isset($errors['name']) ? 'error' : ''; ?>">
        <?php if (isset($errors['name'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['name']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="instructor">Instructor *</label>
        <input type="text" id="instructor" name="instructor" required 
               value="<?php echo htmlspecialchars($_POST['instructor'] ?? ''); ?>"
               class="<?php echo isset($errors['instructor']) ? 'error' : ''; ?>">
        <?php if (isset($errors['instructor'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['instructor']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="schedule_time">Horario *</label>
        <input type="time" id="schedule_time" name="schedule_time" required 
               value="<?php echo htmlspecialchars($_POST['schedule_time'] ?? ''); ?>"
               class="<?php echo isset($errors['schedule_time']) ? 'error' : ''; ?>">
        <?php if (isset($errors['schedule_time'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['schedule_time']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="schedule_days">Días de la Semana *</label>
        <input type="text" id="schedule_days" name="schedule_days" required 
               placeholder="Ej: Lunes, Miércoles, Viernes"
               value="<?php echo htmlspecialchars($_POST['schedule_days'] ?? ''); ?>"
               class="<?php echo isset($errors['schedule_days']) ? 'error' : ''; ?>">
        <?php if (isset($errors['schedule_days'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['schedule_days']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="capacity">Capacidad *</label>
        <input type="number" id="capacity" name="capacity" required min="1" 
               value="<?php echo htmlspecialchars($_POST['capacity'] ?? ''); ?>"
               class="<?php echo isset($errors['capacity']) ? 'error' : ''; ?>">
        <?php if (isset($errors['capacity'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['capacity']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="description">Descripción</label>
        <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="/index.php?controller=class&action=index" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

