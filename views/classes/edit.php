<?php
/**
 * Vista: Editar Clase
 * 
 * CLIENTE: Formulario para editar clase existente
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Editar Clase</h2>

<form method="POST" action="/index.php?controller=class&action=update" class="form">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($class['id']); ?>">
    
    <div class="form-group">
        <label for="name">Nombre de la Clase *</label>
        <input type="text" id="name" name="name" required 
               value="<?php echo htmlspecialchars($class['name']); ?>"
               class="<?php echo isset($errors['name']) ? 'error' : ''; ?>">
        <?php if (isset($errors['name'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['name']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="instructor">Instructor *</label>
        <input type="text" id="instructor" name="instructor" required 
               value="<?php echo htmlspecialchars($class['instructor']); ?>"
               class="<?php echo isset($errors['instructor']) ? 'error' : ''; ?>">
        <?php if (isset($errors['instructor'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['instructor']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="schedule_time">Horario *</label>
        <input type="time" id="schedule_time" name="schedule_time" required 
               value="<?php echo htmlspecialchars(date('H:i', strtotime($class['schedule_time']))); ?>"
               class="<?php echo isset($errors['schedule_time']) ? 'error' : ''; ?>">
        <?php if (isset($errors['schedule_time'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['schedule_time']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="schedule_days">Días de la Semana *</label>
        <input type="text" id="schedule_days" name="schedule_days" required 
               placeholder="Ej: Lunes, Miércoles, Viernes"
               value="<?php echo htmlspecialchars($class['schedule_days']); ?>"
               class="<?php echo isset($errors['schedule_days']) ? 'error' : ''; ?>">
        <?php if (isset($errors['schedule_days'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['schedule_days']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="capacity">Capacidad *</label>
        <input type="number" id="capacity" name="capacity" required min="1" 
               value="<?php echo htmlspecialchars($class['capacity']); ?>"
               class="<?php echo isset($errors['capacity']) ? 'error' : ''; ?>">
        <?php if (isset($errors['capacity'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['capacity']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="description">Descripción</label>
        <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($class['description'] ?? ''); ?></textarea>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="/index.php?controller=class&action=index" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

