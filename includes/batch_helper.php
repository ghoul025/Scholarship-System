<?php
// batch_helper.php
// Helper to list distinct batch values with scholarship types and liquidation status from scholars table
function listBatches($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT TRIM(CAST(batch AS CHAR)) AS batch, scholarship_type, MAX(liquidated) AS liquidated
            FROM scholars 
            WHERE batch IS NOT NULL AND TRIM(CAST(batch AS CHAR)) != ''
            GROUP BY TRIM(CAST(batch AS CHAR)), scholarship_type
            ORDER BY batch DESC, scholarship_type ASC
        ");
        $batches = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Keep batch as is, including decimals (e.g., "13.1")
            $batch_value = $row['batch'];
            if ($batch_value !== '' && is_numeric($batch_value)) {
                $batches[] = [
                    'batch' => $batch_value,
                    'scholarship_type' => $row['scholarship_type'],
                    'liquidated' => (int)$row['liquidated']
                ];
            }
        }
        // Log results for debugging
        file_put_contents('debug.log', 'listBatches results: ' . print_r($batches, true) . "\n", FILE_APPEND);
        return $batches;
    } catch (Exception $e) {
        // Log error
        file_put_contents('debug.log', 'listBatches Error: ' . $e->getMessage() . "\n", FILE_APPEND);
        return [];
    }
}
?>