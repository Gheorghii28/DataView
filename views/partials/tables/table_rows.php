<tbody id="row-list-container">
    <?php include 'row_input_template.php'; ?>
    <?php if (empty($data['rows'])): ?>
        <tr>
            <td colspan="<?php echo count($data['columns']); ?>" class="px-6 py-4 text-center text-gray-500">
                <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300" role="alert"><?php echo htmlspecialchars($data['message']); ?></div>
            </td>
        </tr>
    <?php else: ?>
        <?php foreach ($data['rows'] as $row): ?>
            <?php include 'row_input_template.php'; ?>
            <?php include 'row_template.php'; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>