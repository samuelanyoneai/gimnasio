<?php
/**
 * Vista: Crear Horario
 * 
 * CLIENTE: Formulario que captura datos del usuario y los envía al SERVIDOR
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Nuevo Horario</h2>

<!-- CLIENTE: Formulario que envía datos al SERVIDOR mediante POST -->
<form method="POST" action="/index.php?controller=schedule&action=store" class="form">
    <div class="form-group">
        <label for="day_of_week">Día de la Semana *</label>
        <select id="day_of_week" name="day_of_week" required 
                class="<?php echo isset($errors['day_of_week']) ? 'error' : ''; ?>">
            <option value="">Seleccione un día</option>
            <option value="Lunes" <?php echo ($_POST['day_of_week'] ?? '') === 'Lunes' ? 'selected' : ''; ?>>Lunes</option>
            <option value="Martes" <?php echo ($_POST['day_of_week'] ?? '') === 'Martes' ? 'selected' : ''; ?>>Martes</option>
            <option value="Miércoles" <?php echo ($_POST['day_of_week'] ?? '') === 'Miércoles' ? 'selected' : ''; ?>>Miércoles</option>
            <option value="Jueves" <?php echo ($_POST['day_of_week'] ?? '') === 'Jueves' ? 'selected' : ''; ?>>Jueves</option>
            <option value="Viernes" <?php echo ($_POST['day_of_week'] ?? '') === 'Viernes' ? 'selected' : ''; ?>>Viernes</option>
            <option value="Sábado" <?php echo ($_POST['day_of_week'] ?? '') === 'Sábado' ? 'selected' : ''; ?>>Sábado</option>
            <option value="Domingo" <?php echo ($_POST['day_of_week'] ?? '') === 'Domingo' ? 'selected' : ''; ?>>Domingo</option>
        </select>
        <?php if (isset($errors['day_of_week'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['day_of_week']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="opening_time">Hora de Apertura *</label>
        <input type="time" id="opening_time" name="opening_time" required 
               value="<?php echo htmlspecialchars($_POST['opening_time'] ?? '06:00'); ?>"
               class="<?php echo isset($errors['opening_time']) ? 'error' : ''; ?>">
        <?php if (isset($errors['opening_time'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['opening_time']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="closing_time">Hora de Cierre *</label>
        <input type="time" id="closing_time" name="closing_time" required 
               value="<?php echo htmlspecialchars($_POST['closing_time'] ?? '22:00'); ?>"
               class="<?php echo isset($errors['closing_time']) ? 'error' : ''; ?>">
        <?php if (isset($errors['closing_time'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['closing_time']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="is_active">
            <input type="checkbox" id="is_active" name="is_active" value="1" 
                   <?php echo (isset($_POST['is_active']) || !isset($_POST['day_of_week'])) ? 'checked' : ''; ?>>
            Estado Activo
        </label>
    </div>
    
    <div class="form-group">
        <label for="notes">Notas</label>
        <textarea id="notes" name="notes" rows="3" 
                  placeholder="Notas adicionales sobre el horario..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
    </div>
    
    <div class="form-actions">
        <!-- CLIENTE: Botón que envía datos al SERVIDOR -->
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="/index.php?controller=schedule&action=index" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

