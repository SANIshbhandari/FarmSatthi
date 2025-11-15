<?php
/**
 * Livestock Health Report
 * Shows animal health records, vaccinations, and treatments
 */

$whereConditions = ['1=1'];
$params = [];
$types = '';

if (!empty($filter->dateFrom)) {
    $whereConditions[] = "lh.checkup_date >= ?";
    $params[] = $filter->dateFrom;
    $types .= 's';
}

if (!empty($filter->dateTo)) {
    $whereConditions[] = "lh.checkup_date <= ?";
    $params[] = $filter->dateTo;
    $types .= 's';
}

if (!empty($filter->customFilters['animal_type'])) {
    $whereConditions[] = "l.animal_type = ?";
    $params[] = $filter->customFilters['animal_type'];
    $types .= 's';
}

$whereClause = implode(' AND ', $whereConditions);

$query = "
    SELECT 
        lh.animal_id,
        l.animal_type,
        lh.breed,
        lh.age_months,
        lh.weight_kg,
        lh.vaccination_date,
        lh.vaccination_type,
        lh.disease_history,
        lh.medication_treatment,
        lh.checkup_date,
        lh.veterinarian_name,
        lh.next_checkup_date
    FROM livestock_health lh
    INNER JOIN livestock l ON lh.livestock_id = l.id
    WHERE $whereClause
    ORDER BY lh.checkup_date DESC
";

$result = $reportGen->executeQuery($query, $params, $types);

$summaryQuery = "
    SELECT 
        COUNT(DISTINCT lh.livestock_id) as total_animals,
        COUNT(*) as total_checkups,
        SUM(CASE WHEN lh.vaccination_date IS NOT NULL THEN 1 ELSE 0 END) as vaccinated_count,
        SUM(CASE WHEN lh.disease_history IS NOT NULL AND lh.disease_history != '' THEN 1 ELSE 0 END) as disease_cases
    FROM livestock_health lh
    INNER JOIN livestock l ON lh.livestock_id = l.id
    WHERE $whereClause
";

$summaryResult = $reportGen->executeQuery($summaryQuery, $params, $types);
$summary = $summaryResult ? $summaryResult->fetch_assoc() : [];
?>

<h3>Livestock Health Report</h3>

<div class="report-summary">
    <div class="summary-card">
        <h4>Animals Monitored</h4>
        <p class="summary-number"><?php echo number_format($summary['total_animals'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Total Checkups</h4>
        <p class="summary-number"><?php echo number_format($summary['total_checkups'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Vaccinations</h4>
        <p class="summary-number text-success"><?php echo number_format($summary['vaccinated_count'] ?? 0); ?></p>
    </div>
    <div class="summary-card">
        <h4>Disease Cases</h4>
        <p class="summary-number text-warning"><?php echo number_format($summary['disease_cases'] ?? 0); ?></p>
    </div>
</div>

<div class="export-actions">
    <button onclick="exportToCSV()" class="btn btn-secondary">📥 Export to CSV</button>
    <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Report</button>
</div>

<?php if ($result && $result->num_rows > 0): ?>
<div class="table-responsive">
    <table class="data-table" id="healthTable">
        <thead>
            <tr>
                <th>Animal ID</th>
                <th>Type</th>
                <th>Breed</th>
                <th>Age (months)</th>
                <th>Weight (kg)</th>
                <th>Vaccination</th>
                <th>Disease History</th>
                <th>Treatment</th>
                <th>Checkup Date</th>
                <th>Veterinarian</th>
                <th>Next Checkup</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['animal_id'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($row['animal_type']); ?></td>
                <td><?php echo htmlspecialchars($row['breed'] ?? 'N/A'); ?></td>
                <td><?php echo number_format($row['age_months'] ?? 0); ?></td>
                <td><?php echo number_format($row['weight_kg'] ?? 0, 2); ?></td>
                <td>
                    <?php if ($row['vaccination_date']): ?>
                        <?php echo htmlspecialchars($row['vaccination_type']); ?><br>
                        <small><?php echo formatDate($row['vaccination_date']); ?></small>
                    <?php else: ?>
                        <span class="text-muted">None</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['disease_history'] ?? 'None'); ?></td>
                <td><?php echo htmlspecialchars($row['medication_treatment'] ?? 'None'); ?></td>
                <td><?php echo formatDate($row['checkup_date']); ?></td>
                <td><?php echo htmlspecialchars($row['veterinarian_name'] ?? 'N/A'); ?></td>
                <td>
                    <?php 
                    if ($row['next_checkup_date']) {
                        $nextDate = strtotime($row['next_checkup_date']);
                        $today = strtotime(date('Y-m-d'));
                        $class = $nextDate < $today ? 'text-danger' : 'text-success';
                        echo '<span class="' . $class . '">' . formatDate($row['next_checkup_date']) . '</span>';
                    } else {
                        echo 'Not scheduled';
                    }
                    ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="no-results">
    <p>No health records available for the selected filters.</p>
</div>
<?php endif; ?>

<script>
function exportToCSV() {
    const table = document.getElementById('healthTable');
    let csv = 'Livestock Health Report\n\n';
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => headers.push(th.textContent));
    csv += headers.join(',') + '\n';
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            let text = td.textContent.replace(/,/g, ';').replace(/\n/g, ' ').trim();
            row.push('"' + text + '"');
        });
        csv += row.join(',') + '\n';
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'livestock_health_report_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}
</script>
