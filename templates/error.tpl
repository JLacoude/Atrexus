<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Error</title>
  </head>
  <body>
    <h1>An error happened</h1>
    <?php if($platform == 'dev'): ?>
    <p>In <?php echo htmlspecialchars($file, ENT_COMPAT, 'UTF-8');?> at line <?php echo htmlspecialchars($line, ENT_COMPAT, 'UTF-8');?><p>
    <p>Message: <?php echo htmlspecialchars($message, ENT_COMPAT, 'UTF-8');?></p>
    <p>trace: <pre><?php echo htmlspecialchars(print_r(debug_backtrace(), true), ENT_COMPAT, 'UTF-8');?></pre></p>
    <?php else: ?>
    <p>Contact the admin to report it.</p>
    <?php endif; ?>
  </body>
</html>
