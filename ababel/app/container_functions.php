<?php
/**
 * Container Database Functions
 * Include this file in update_container_status.php
 */

/**
 * Get container by ID
 * @param mysqli $conn Database connection
 * @param int $container_id Container ID
 * @return array|null Container data or null if not found
 */
function getContainerById($conn, $container_id) {
    $sql = "SELECT c.*, 
            c.client_name,
            c.bill_of_lading_status,
            c.tashitim_status,
            c.release_status,
            c.bill_number,
            c.carrier,
            c.ship_name,
            c.custom_station,
            c.expected_arrival,
            c.notes,
            c.entry_date,
            c.container_number,
            c.loading_number,
            c.carton_count,
            c.category,
            c.registry,
            c.weight,
            c.unloading_place,
            c.company_release,
            c.office,
            c.bill_of_lading_date,
            c.bill_of_lading_file,
            c.tashitim_date
            FROM containers c
            WHERE c.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $container_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Get operational statuses for a container
 * @param mysqli $conn Database connection
 * @param int $container_id Container ID
 * @return array Array of operational statuses
 */
function getOperationalStatuses($conn, $container_id) {
    $sql = "SELECT * FROM container_operational_status 
            WHERE container_id = ? 
            ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $container_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $statuses = [];
    while ($row = $result->fetch_assoc()) {
        $statuses[] = $row;
    }
    
    return $statuses;
}

/**
 * Get position history for a container
 * @param mysqli $conn Database connection
 * @param int $container_id Container ID
 * @return array Array of position history
 */
function getPositionHistory($conn, $container_id) {
    $sql = "SELECT * FROM container_position_history 
            WHERE container_id = ? 
            ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $container_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $positions = [];
    while ($row = $result->fetch_assoc()) {
        $positions[] = $row;
    }
    
    return $positions;
}

/**
 * Get timeline events for a container
 * @param mysqli $conn Database connection
 * @param int $container_id Container ID
 * @return array Array of timeline events
 */
function getTimelineEvents($conn, $container_id) {
    // Get container data first
    $container = getContainerById($conn, $container_id);
    
    // Get operational statuses
    $operational_statuses = getOperationalStatuses($conn, $container_id);
    
    // Create a map of operational statuses for easy access
    $status_map = [];
    foreach ($operational_statuses as $status) {
        $status_map[$status['status']] = $status;
    }
    
    // Define timeline events based on your business logic
    $events = [];
    
    // 1. Container Entry
    $events[] = [
        'title' => 'استلام الحاوية',
        'description' => 'تم استلام الحاوية في الميناء',
        'icon' => 'bi-box-arrow-in-down',
        'status_class' => !empty($container['entry_date']) ? 'completed' : 'pending',
        'date' => $container['entry_date'] ?: null,
        'file_path' => null
    ];
    
    // 2. Bill of Lading
    $bill_status_class = 'pending';
    $bill_date = null;
    $bill_file = null;
    
    if ($container['bill_of_lading_status'] === 'issued') {
        $bill_status_class = 'completed';
        $bill_date = $container['bill_of_lading_date'];
        $bill_file = $container['bill_of_lading_file'];
    } elseif ($container['bill_of_lading_status'] === 'delayed') {
        $bill_status_class = 'delayed';
    }
    
    // Also check operational status for Bill of Lading
    if (isset($status_map['Bill of Lading Issued'])) {
        $bill_status_class = 'completed';
        $bill_date = $status_map['Bill of Lading Issued']['date'] ?: $status_map['Bill of Lading Issued']['created_at'];
        $bill_file = $status_map['Bill of Lading Issued']['file_path'] ?: $bill_file;
    }
    
    $events[] = [
        'title' => 'إصدار البوليصة',
        'description' => 'تم إصدار بوليصة الشحن',
        'icon' => 'bi-file-text',
        'status_class' => $bill_status_class,
        'date' => $bill_date,
        'file_path' => $bill_file
    ];
    
    // 3. Customs Clearance (Tashitim)
    $customs_status_class = 'pending';
    $customs_date = null;
    $customs_file = null;
    
    if ($container['tashitim_status'] === 'done') {
        $customs_status_class = 'completed';
        $customs_date = $container['tashitim_date'];
    } elseif ($container['tashitim_status'] === 'delayed') {
        $customs_status_class = 'delayed';
    }
    
    // Check operational status for Customs
    if (isset($status_map['Customs Cleared'])) {
        $customs_status_class = 'completed';
        $customs_date = $status_map['Customs Cleared']['date'] ?: $status_map['Customs Cleared']['created_at'];
        $customs_file = $status_map['Customs Cleared']['file_path'];
    }
    
    $events[] = [
        'title' => 'التختيم الجمركي',
        'description' => 'تم التختيم الجمركي',
        'icon' => 'bi-stamp',
        'status_class' => $customs_status_class,
        'date' => $customs_date,
        'file_path' => $customs_file
    ];
    
    // 4. Release Status
    $events[] = [
        'title' => 'الإفراج',
        'description' => 'تم الإفراج عن الحاوية',
        'icon' => 'bi-unlock',
        'status_class' => $container['release_status'] === 'Yes' ? 'completed' : 'pending',
        'date' => $container['release_status'] === 'Yes' ? date('Y-m-d') : null,
        'file_path' => null
    ];
    
    // 5. Delivery Status - check from position history
    $position_history = getPositionHistory($conn, $container_id);
    $delivery_status_class = 'pending';
    $delivery_date = null;
    
    foreach ($position_history as $position) {
        if ($position['status'] === 'Delivered' || $position['status'] === 'Empty Returned') {
            $delivery_status_class = 'completed';
            $delivery_date = date('Y-m-d', strtotime($position['created_at']));
            break;
        }
    }
    
    $events[] = [
        'title' => 'التسليم',
        'description' => 'تم تسليم البضاعة للعميل',
        'icon' => 'bi-check-circle',
        'status_class' => $delivery_status_class,
        'date' => $delivery_date,
        'file_path' => null
    ];
    
    return $events;
}

/**
 * Calculate progress percentage based on completed steps
 * @param array $container Container data
 * @param array $operational_statuses Operational statuses
 * @param array $position_history Position history
 * @return int Progress percentage
 */
function calculateProgress($container, $operational_statuses, $position_history) {
    $total_steps = 5; // Total number of major steps
    $completed_steps = 0;
    
    // Check entry date
    if (!empty($container['entry_date'])) {
        $completed_steps++;
    }
    
    // Check bill of lading status
    if ($container['bill_of_lading_status'] === 'issued') {
        $completed_steps++;
    }
    
    // Check tashitim status
    if ($container['tashitim_status'] === 'done') {
        $completed_steps++;
    }
    
    // Check release status
    if ($container['release_status'] === 'Yes') {
        $completed_steps++;
    }
    
    // Check if delivered (from position history)
    $latest_position = getLatestPosition($position_history);
    if ($latest_position === 'Delivered' || $latest_position === 'Empty Returned') {
        $completed_steps++;
    }
    
    return round(($completed_steps / $total_steps) * 100);
}

/**
 * Get the latest position from position history
 * @param array $position_history Position history array
 * @return string Latest position or 'Unknown'
 */
function getLatestPosition($position_history) {
    if (!empty($position_history) && isset($position_history[0]['status'])) {
        return $position_history[0]['status'];
    }
    return 'Unknown';
}

/**
 * Get CSS class for status badge
 * @param string $status Status value
 * @return string CSS class
 */
function getStatusClass($status) {
    switch ($status) {
        case 'done':
        case 'issued':
        case 'Yes':
        case 'completed':
            return 'status-completed';
        case 'delayed':
            return 'status-delayed';
        default:
            return 'status-pending';
    }
}

/**
 * Get status text in Arabic
 * @param string $type Status type
 * @param string $status Status value
 * @return string Status text in Arabic
 */
function getStatusText($type, $status) {
    $texts = [
        'bill_of_lading' => [
            'not_issued' => 'لم يتم الإصدار',
            'issued' => 'تم الإصدار',
            'delayed' => 'متأخر'
        ],
        'tashitim' => [
            'not_done' => 'لم يتم التختيم',
            'done' => 'تم التختيم',
            'delayed' => 'متأخر'
        ]
    ];
    
    return isset($texts[$type][$status]) ? $texts[$type][$status] : $status;
}

/**
 * Translate status to Arabic
 * @param string $status Status in English
 * @return string Status in Arabic
 */
function translateStatus($status) {
    $translations = [
        'Loaded' => 'تم التحميل',
        'At Port' => 'في الميناء',
        'At Sea' => 'في البحر',
        'Arrived' => 'وصلت',
        'Transported by Land' => 'تم الشحن البري',
        'Delivered' => 'تم التسليم',
        'Empty Returned' => 'تم تسليم الفارغ',
        'Unknown' => 'غير محدد'
    ];
    
    return isset($translations[$status]) ? $translations[$status] : $status;
}

/**
 * Handle API requests
 * This function should be implemented based on your API requirements
 */
function handleApiRequest() {
    global $conn;
    
    header('Content-Type: application/json');
    
    // Get request method and parameters
    $method = $_SERVER['REQUEST_METHOD'];
    $container_id = filter_input(INPUT_GET, 'container_id', FILTER_VALIDATE_INT);
    
    if (!$container_id) {
        echo json_encode(['error' => 'Invalid container ID']);
        return;
    }
    
    switch ($method) {
        case 'GET':
            // Get container status
            $container = getContainerById($conn, $container_id);
            if ($container) {
                $operational_statuses = getOperationalStatuses($conn, $container_id);
                $position_history = getPositionHistory($conn, $container_id);
                $timeline_events = getTimelineEvents($conn, $container_id);
                $progress = calculateProgress($container, $operational_statuses, $position_history);
                
                echo json_encode([
                    'container' => $container,
                    'operational_statuses' => $operational_statuses,
                    'position_history' => $position_history,
                    'timeline_events' => $timeline_events,
                    'progress' => $progress
                ]);
            } else {
                echo json_encode(['error' => 'Container not found']);
            }
            break;
            
        case 'POST':
            // Update container status
            // Implementation would go here
            echo json_encode(['error' => 'Not implemented']);
            break;
            
        default:
            echo json_encode(['error' => 'Method not allowed']);
    }
}