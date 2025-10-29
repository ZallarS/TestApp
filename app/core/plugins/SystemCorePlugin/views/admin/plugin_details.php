<?php
// app/views/admin/plugin_details.php
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <style>
        .dependency-section { margin: 20px 0; }
        .dependency-list { list-style: none; padding: 0; }
        .dependency-item {
            padding: 10px;
            margin: 5px 0;
            border-left: 4px solid #3498db;
            background: #f8f9fa;
        }
        .dependency-item.satisfied { border-color: #2ecc71; }
        .dependency-item.missing { border-color: #e74c3c; }
        .dependency-item.warning { border-color: #f39c12; }
        .status-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        .status-active { background: #2ecc71; color: white; }
        .status-inactive { background: #95a5a6; color: white; }
        .status-missing { background: #e74c3c; color: white; }
    </style>
</head>
<body>
<h1><?php echo $title; ?></h1>

<div class="plugin-info">
    <h2>Plugin Information</h2>
    <p><strong>Name:</strong> <?php echo $plugin->getName(); ?></p>
    <p><strong>Version:</strong> <?php echo $plugin->getVersion(); ?></p>
    <p><strong>Description:</strong> <?php echo $plugin->getDescription(); ?></p>
    <p><strong>Status:</strong>
        <?php if ($is_active): ?>
            <span class="status-badge status-active">Active</span>
        <?php else: ?>
            <span class="status-badge status-inactive">Inactive</span>
        <?php endif; ?>
    </p>
</div>

<!-- Dependencies -->
<div class="dependency-section">
    <h2>Dependencies</h2>
    <?php if (empty($dependency_info['dependencies'])): ?>
        <p>No dependencies</p>
    <?php else: ?>
        <ul class="dependency-list">
            <?php foreach ($dependency_info['dependencies'] as $dep): ?>
                <li class="dependency-item <?php echo $dep['satisfied'] ? 'satisfied' : 'missing'; ?>">
                    <strong><?php echo $dep['name']; ?></strong> (requires <?php echo $dep['constraint']; ?>)
                    <?php if ($dep['installed']): ?>
                        <?php if ($dep['active']): ?>
                            <span class="status-badge status-active">Active</span>
                        <?php else: ?>
                            <span class="status-badge status-inactive">Inactive</span>
                        <?php endif; ?>
                        <span>Version: <?php echo $dep['version']; ?></span>
                    <?php else: ?>
                        <span class="status-badge status-missing">Missing</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- Dependents -->
<div class="dependency-section">
    <h2>Dependent Plugins</h2>
    <?php if (empty($dependents)): ?>
        <p>No plugins depend on this plugin</p>
    <?php else: ?>
        <ul class="dependency-list">
            <?php foreach ($dependents as $dependent): ?>
                <li class="dependency-item">
                    <strong><?php echo $dependent['name']; ?></strong>
                    (requires <?php echo $dependent['constraint']; ?>)
                    <?php if ($dependent['active']): ?>
                        <span class="status-badge status-active">Active</span>
                    <?php else: ?>
                        <span class="status-badge status-inactive">Inactive</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- Actions -->
<div class="actions">
    <?php if (!$is_active): ?>
        <form method="POST" action="/admin/plugins/activate-with-deps">
            <input type="hidden" name="plugin_name" value="<?php echo $plugin->getName(); ?>">
            <button type="submit">Activate with Dependencies</button>
        </form>
    <?php else: ?>
        <form method="POST" action="/admin/plugins/toggle">
            <input type="hidden" name="plugin_name" value="<?php echo $plugin->getName(); ?>">
            <input type="hidden" name="action" value="deactivate">
            <button type="submit">Deactivate</button>
        </form>
    <?php endif; ?>

    <a href="/admin">Back to Plugins</a>
</div>
</body>
</html>