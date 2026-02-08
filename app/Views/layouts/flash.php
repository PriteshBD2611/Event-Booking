<?php
// Display flash messages
if (isset($_SESSION['flash'])):
    $flash = $_SESSION['flash'];
    $alertClass = $flash['type'] === 'error' ? 'alert-danger' : 'alert-' . $flash['type'];
?>
<div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($flash['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php
    unset($_SESSION['flash']);
endif;
?>
