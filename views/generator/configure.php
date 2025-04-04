<?php
$title = 'Configure MVC Generator';
require_once VIEWS_PATH . '/layouts/header.php';
?>

<div class="container mt-4">
    <h1>Configure MVC Generator - <?= ucfirst($table) ?></h1>
    
    <form method="POST" action="/generator/generate">
        <input type="hidden" name="table" value="<?= $table ?>">
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Fields Configuration</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Field Name</th>
                                <th>Type</th>
                                <th>Validation Rules</th>
                                <th>Include in Form</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fields as $field): ?>
                                <tr>
                                    <td><?= $field['name'] ?></td>
                                    <td><?= $field['type'] ?></td>
                                    <td>
                                        <input type="text" 
                                               name="validation[<?= $field['name'] ?>]" 
                                               class="form-control" 
                                               placeholder="e.g. required|min:3|max:255"
                                               value="<?= isset($config['validation'][$field['name']]) ? $config['validation'][$field['name']] : '' ?>">
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   name="include[<?= $field['name'] ?>]" 
                                                   class="form-check-input" 
                                                   value="1" 
                                                   <?= isset($config['include'][$field['name']]) ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if (!empty($relations)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Relationships</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Related Table</th>
                                <th>Type</th>
                                <th>Include</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($relations as $relation): ?>
                                <tr>
                                    <td><?= $relation['referenced_table'] ?></td>
                                    <td>belongsTo</td>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   name="relations[<?= $relation['referenced_table'] ?>]" 
                                                   class="form-check-input" 
                                                   value="1" 
                                                   <?= isset($config['relations'][$relation['referenced_table']]) ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Generate MVC</button>
            <a href="/generator" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?> 