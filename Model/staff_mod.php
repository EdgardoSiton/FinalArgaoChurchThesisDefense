<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../Controller/phpmailer/src/PHPMailer.php'; // Ensure this uses require_once
require_once __DIR__ . '/../Controller/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../Controller/phpmailer/src/Exception.php';
require_once  __DIR__ . '/../vendor/autoload.php';
class Staff {
    private $conn;
    private $regId;

    public function countPendingCitizenAccounts() {
        $query = "SELECT COUNT(*) as pending_count FROM `citizen` WHERE r_status = 'Pending'";
        $stmt = $this->conn->prepare($query);
        $pendingCount = 0;
    
        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($pending_count);
            $stmt->fetch();
            $pendingCount = $pending_count ?? 0;
            $stmt->close();
        }
    
        return $pendingCount;
    }
    
    public function __construct($conn, $regId = null) {
        $this->conn = $conn;
        $this->regId = $regId;
        $this->updatePendingAppointments();
        $this->deleteExpiredAnnouncements();
    }  
    public function countPendingAppointments() {
        $tables = ['baptismfill', 'confirmationfill', 'defuctomfill', 'marriagefill'];
        $totalPending = 0;
    
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) as pending_count FROM `$table` WHERE status = 'Pending' AND event_name NOT LIKE '%Mass%'";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt) {
                $stmt->execute();
                $stmt->bind_result($pending_count);
                $stmt->fetch();
                $totalPending += $pending_count ?? 0;
                $stmt->close();
            }
        }
    
        return $totalPending;
    }
    public function countPendingRequestForms() {
        $query = "SELECT COUNT(*) as pending_count FROM `req_form` WHERE status = 'Pending'";
        $stmt = $this->conn->prepare($query);
        $pendingCount = 0;
    
        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($pending_count);
            $stmt->fetch();
            $pendingCount = $pending_count ?? 0;
            $stmt->close();
        }
    
        return $pendingCount;
    }
    public function countApprovePriestForms() {
        $tables = ['baptismfill', 'confirmationfill', 'defuctomfill', 'marriagefill', 'req_form'];
        $totalApproved = 0;
    
        foreach ($tables as $table) {
            // Build the base query
            $query = "
                SELECT COUNT(*) as approved_count 
                FROM `$table`
                LEFT JOIN `priest_approval` ON `$table`.approval_id = `priest_approval`.approval_id 
                WHERE `priest_approval`.pr_status = 'Approved'
                AND `$table`.status = 'Pending'
            ";
            
            // Add event_name condition for all tables except 'req_form'
            if ($table !== 'req_form') {
                $query .= " AND `$table`.event_name NOT LIKE '%Mass%'";
            }
            
            // Prepare and execute the query
            $stmt = $this->conn->prepare($query);
            
            if ($stmt) {
                $stmt->execute();
                $stmt->bind_result($approved_count);
                $stmt->fetch();
                $totalApproved += $approved_count ?? 0;
                $stmt->close();
            }
        }
    
        return $totalApproved;
    }
    
    
    
    public function countPendingMassAppointments() {
        $tables = ['baptismfill', 'confirmationfill', 'defuctomfill', 'marriagefill'];
        $totalPendings = 0;
    
        foreach ($tables as $table) {
            $query = "SELECT COUNT(*) as pending_count FROM `$table` WHERE status = 'Pending' AND event_name LIKE '%Mass%'";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt) {
                $stmt->execute();
                $stmt->bind_result($pending_count);
                $stmt->fetch();
                $totalPendings += $pending_count ?? 0;
                $stmt->close();
            }
        }
    
        return $totalPendings;
    }
    

    
    
    public function addMassSchedule($cal_date, $startTime, $endTime, $priest_id) {
        // Start transaction
        mysqli_begin_transaction($this->conn);

        try {
            // Step 1: Insert into schedule table
            $scheduleSql = "INSERT INTO schedule(date, start_time, end_time) VALUES (?, ?, ?)";
            $stmtSchedule = mysqli_prepare($this->conn, $scheduleSql);
            mysqli_stmt_bind_param($stmtSchedule, 'sss', $cal_date, $startTime, $endTime);
            mysqli_stmt_execute($stmtSchedule);

            // Get the last inserted schedule_id
            $schedule_id = mysqli_insert_id($this->conn);

            // Step 2: Insert into priest_approval table
            $priestApprovalSql = "INSERT INTO priest_approval(priest_id, pr_status, assigned_time) VALUES (?, 'Approved', NOW())";
            $stmtApproval = mysqli_prepare($this->conn, $priestApprovalSql);
            mysqli_stmt_bind_param($stmtApproval, 's', $priest_id);
            mysqli_stmt_execute($stmtApproval);

            // Get the last inserted approval_id
            $approval_id = mysqli_insert_id($this->conn);

            // Step 3: Insert into mass_schedule table
            $massScheduleSql = "INSERT INTO mass_schedule(schedule_id, approval_id, mass_title) VALUES (?, ?, 'Mass')";
            $stmtMassSchedule = mysqli_prepare($this->conn, $massScheduleSql);
            mysqli_stmt_bind_param($stmtMassSchedule, 'ii', $schedule_id, $approval_id);
            mysqli_stmt_execute($stmtMassSchedule);

            // Commit transaction if all inserts were successful
            mysqli_commit($this->conn);
            return 'Event added successfully';
        } catch (Exception $e) {
            // Rollback transaction if an error occurs
            mysqli_rollback($this->conn);
            return 'Error: ' . $e->getMessage();
        } finally {
            // Close statements
            if (isset($stmtSchedule)) {
                mysqli_stmt_close($stmtSchedule);
            }
            if (isset($stmtApproval)) {
                mysqli_stmt_close($stmtApproval);
            }
            if (isset($stmtMassSchedule)) {
                mysqli_stmt_close($stmtMassSchedule);
            }
        }
    } 
    public function generatemassconfirmationSeminarReport($eventType) {
        $stmt = $this->conn->prepare("
            SELECT 
                cf.confirmationfill_id, 
                cf.fullname AS fullnames, 
                ass.p_status, 
                cf.event_name AS type,
                ass.reference_number AS ref_number, 
                ass.payable_amount, 
                s1.date AS appointment_schedule_date,
                s2.date AS seminar_date,
                priest.fullname AS priest_fullname
            FROM 
                announcement a
            LEFT JOIN 
                confirmationfill cf ON cf.announcement_id = a.announcement_id
                LEFT JOIN 
               appointment_schedule ass ON cf.confirmationfill_id = ass.confirmation_id
            LEFT JOIN 
                schedule s1 ON a.schedule_id = s1.schedule_id -- For regular schedule
            LEFT JOIN 
                schedule s2 ON a.seminar_id = s2.schedule_id -- For seminar schedule
            LEFT JOIN 
                priest_approval pa ON pa.approval_id = a.approval_id
            LEFT JOIN 
                citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
            WHERE 
                a.event_type = ? 
                AND cf.status = 'Approved'
        ");
        
        $stmt->bind_param('s', $eventType);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function generatemassmarriageSeminarReport($eventType) {
        $stmt = $this->conn->prepare("
            SELECT 
                mf.marriagefill_id, 
                CONCAT(mf.groom_name, ' & ', mf.bride_name) AS fullnames,
                ass.p_status, 
                mf.event_name AS type,
                ass.reference_number AS ref_number, 
                ass.payable_amount, 
                s1.date AS appointment_schedule_date,
                s2.date AS seminar_date,
                priest.fullname AS priest_fullname
            FROM 
                announcement a
            LEFT JOIN 
                marriagefill mf ON mf.announcement_id = a.announcement_id
                LEFT JOIN 
               appointment_schedule ass ON mf.marriagefill_id = ass.marriage_id
            LEFT JOIN 
                schedule s1 ON a.schedule_id = s1.schedule_id -- For regular schedule
            LEFT JOIN 
                schedule s2 ON a.seminar_id = s2.schedule_id -- For seminar schedule
            LEFT JOIN 
                priest_approval pa ON pa.approval_id = a.approval_id
            LEFT JOIN 
                citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
            WHERE 
                a.event_type = ? 
                AND mf.status = 'Approved'
        ");
        
        $stmt->bind_param('s', $eventType);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
public function generateSeminarReport($eventType) {
    $stmt = $this->conn->prepare("
        SELECT 
        a.speaker_ann AS speaker,
            bf.baptism_id, 
            bf.fullname AS fullnames, 
            ass.p_status, 
            bf.event_name AS type,
            ass.reference_number AS ref_number, 
            ass.payable_amount, 
            s1.date AS appointment_schedule_date,
            s2.date AS seminar_date,
            priest.fullname AS priest_fullname
        FROM 
            announcement a
        LEFT JOIN 
            baptismfill bf ON bf.announcement_id = a.announcement_id
            LEFT JOIN 
           appointment_schedule ass ON bf.baptism_id = ass.baptismfill_id
        LEFT JOIN 
            schedule s1 ON a.schedule_id = s1.schedule_id -- For regular schedule
        LEFT JOIN 
            schedule s2 ON a.seminar_id = s2.schedule_id -- For seminar schedule
        LEFT JOIN 
            priest_approval pa ON pa.approval_id = a.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
        WHERE 
            a.event_type = ? 
            AND bf.status = 'Approved'
    ");
    
    $stmt->bind_param('s', $eventType);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

    
    private function deleteExpiredAnnouncements() {
        // SQL statement to delete announcements based on the schedule date and end time
        $deleteSql = "
        DELETE a, s, se FROM announcement a
        JOIN schedule s ON a.schedule_id = s.schedule_id
        LEFT JOIN schedule se ON a.seminar_id = se.schedule_id
        WHERE DATE(s.date) = CURDATE() AND NOW() > s.end_time
        ";
    
        if ($deleteStmt = $this->conn->prepare($deleteSql)) {
            $deleteStmt->execute();
            $deleteStmt->close();
        } else {
            // Log error or handle it as needed
            // echo 'Error preparing statement: ' . $this->conn->error;
        }
    }
    
    private function updatePendingAppointments() {
        // SQL query for baptismfill
        $sqlBaptism = "
            UPDATE priest_approval pa
           
            SET pa.pr_status = NULL, pa.priest_id = NULL
            WHERE pa.pr_status = 'Pending'
            AND (pa.assigned_time < NOW() - INTERVAL 24 HOUR);
        ";
    
        // SQL query for confirmationfill
        $sqlConfirmation = "
        UPDATE priest_approval pa
           
        SET pa.pr_status = NULL, pa.priest_id = NULL
        WHERE pa.pr_status = 'Pending'
        AND (pa.assigned_time < NOW() - INTERVAL 24 HOUR);
        ";
    
        // SQL query for marriagefill
        $sqlMarriage = "
        UPDATE priest_approval pa
           
        SET pa.pr_status = NULL, pa.priest_id = NULL
        WHERE pa.pr_status = 'Pending'
        AND (pa.assigned_time < NOW() - INTERVAL 24 HOUR);
        ";
    
        // SQL query for defuctomfill
        $sqlDefuctom = "
        UPDATE priest_approval pa
           
        SET pa.pr_status = NULL, pa.priest_id = NULL
        WHERE pa.pr_status = 'Pending'
        AND (pa.assigned_time < NOW() - INTERVAL 24 HOUR);
        ";
    
        // SQL query for req_form
        $sqlRequestForm = "
        UPDATE priest_approval pa
           
        SET pa.pr_status = NULL, pa.priest_id = NULL
        WHERE pa.pr_status = 'Pending'
        AND (pa.assigned_time < NOW() - INTERVAL 24 HOUR);
        ";
    
        // Execute each query separately
        $this->conn->prepare($sqlBaptism)->execute();
        $this->conn->prepare($sqlConfirmation)->execute();
        $this->conn->prepare($sqlMarriage)->execute();
        $this->conn->prepare($sqlDefuctom)->execute();
        $this->conn->prepare($sqlRequestForm)->execute();
    }
    public function generateWeddingReport() {
        // SQL query to fetch wedding report data
        $sql = "
            SELECT 
            a.speaker_app AS Speaker,
                a.reference_number AS ref_number,
                'Marriage' AS type,
                mf.status AS status,
                mf.groom_name AS fullnames,
                pa.pr_status AS approve_priest,
                mf.marriagefill_id AS id,
                mf.role AS roles,
                mf.event_name AS Event_Name,
                c.fullname AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_time,
                a.appsched_id,
                a.marriage_id,
               pa.priest_id,
                priest.fullname AS priest_name,
                a.schedule_id AS appointment_schedule_id,
                a.payable_amount AS payable_amount,
                a.status AS c_status,
                a.p_status AS p_status,
                sch.date AS appointment_schedule_date,  
                sch.start_time AS appointment_schedule_start_time,
                sch.end_time AS appointment_schedule_end_time,
                mf.m_created_at 
            FROM 
                schedule s
            LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
            JOIN marriagefill mf ON s.schedule_id = mf.schedule_id
            JOIN appointment_schedule a ON mf.marriagefill_id = a.marriage_id
            JOIN schedule sch ON a.schedule_id = sch.schedule_id
            JOIN priest_approval pa ON pa.approval_id = mf.approval_id
            LEFT JOIN citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
            WHERE 
                mf.status != 'Pending' 
                AND mf.status = 'Approved'
                AND sch.date >= CURRENT_DATE -- Get the schedule for today or the future
            ORDER BY sch.date ASC -- Order by date to get the nearest one
            LIMIT 1; -- Get only the nearest schedule
        ";
    
        // Prepare and execute the SQL statement
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    
        // Fetch the result using mysqli
        $result = $stmt->get_result(); // Get the result set from the prepared statement
        $report = $result->fetch_assoc(); // Fetch a single result as an associative array
    
        // Check if a result exists
        if (!$report) {
            return []; // Return an empty array if no results found
        }
    
        // Return the fetched result directly
        return $report; // Return the result directly (can be formatted later if needed)
    }
    
    public function generateBaptismReport() {
        // Get the current week's start (Monday) and end (Sunday) dates
        $currentWeekStart = date('Y-m-d', strtotime('monday this week'));
        $currentWeekEnd = date('Y-m-d', strtotime('sunday this week'));
    
        // Define the SQL query to get Baptism appointments for the current week
            $sql = "SELECT 
               a.speaker_app AS speaker,
                a.reference_number AS ref_number,
                'Baptism' AS type,
                b.status AS status,
                b.fullname AS fullnames,
                pa.pr_status AS approve_priest,
                b.baptism_id AS id,
                b.role AS roles,
                b.event_name AS Event_Name,
                c.fullname AS citizen_name, 
                s.date AS schedule_date,
                s.start_time AS schedule_time,
                a.appsched_id,
                a.baptismfill_id,
                pa.priest_id,
                priest.fullname AS priest_name,
                a.schedule_id AS appointment_schedule_id,
                a.payable_amount AS payable_amount,
                a.status AS c_status,
                a.p_status AS p_status,
                sch.date AS appointment_schedule_date,  
                sch.start_time AS appointment_schedule_start_time,
                sch.end_time AS appointment_schedule_end_time,
                b.created_at 
            FROM 
                schedule s
            LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
            JOIN baptismfill b ON s.schedule_id = b.schedule_id
            JOIN appointment_schedule a ON b.baptism_id = a.baptismfill_id
            JOIN schedule sch ON a.schedule_id = sch.schedule_id  
            JOIN priest_approval pa ON pa.approval_id = b.approval_id
        LEFT JOIN citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
            WHERE 
                b.status != 'Pending' AND 
                b.status = 'Approved' AND 
                (a.status = 'Process' OR a.p_status = 'Unpaid')
                AND sch.date BETWEEN ? AND ?";
        
            // Prepare the SQL statement
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                die('Error preparing statement: ' . $this->conn->error);
            }
        
            // Bind parameters (use 'ss' for two string parameters)
            $stmt->bind_param('ss', $currentWeekStart, $currentWeekEnd);
        
            // Execute the query
            $stmt->execute();
        
            // Fetch the results
            $result = $stmt->get_result();
            $reports = $result->fetch_all(MYSQLI_ASSOC);
        
            return $reports;
        }
    
    
    public function getRequestAppointment($statusFilter = null) {
        // Base SQL query with INNER JOIN, LEFT JOIN on related tables
        $query = "
            SELECT 
            
                pa.pr_status AS approve_priest,
                s.schedule_id, 
                s.citizen_id, 
                s.date, 
                s.start_time, 
                s.end_time, 
                s.event_type, 
                r.req_id, 
                r.req_name_pamisahan, 
                r.req_address, 
                r.req_category, 
                r.req_person, 
                r.req_pnumber, 
                r.cal_date, 
                r.req_chapel, 
                r.status AS req_status, 
                r.role, 
                r.created_at, 
                a.appsched_id, 
                a.baptismfill_id, 
                a.confirmation_id, 
                a.defuctom_id, 
                a.marriage_id, 
                a.schedule_id AS app_schedule_id, 
                a.request_id, 
                a.payable_amount, 
                a.status AS c_status, 
                a.p_status, 
                priest.fullname AS priest_name,  
                a.reference_number AS ref_number
            FROM 
                schedule s
            LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
            JOIN req_form r ON s.schedule_id = r.schedule_id
            JOIN appointment_schedule a ON r.req_id = a.request_id
            LEFT JOIN 
            priest_approval pa ON pa.approval_id = r.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
            WHERE 
                r.status = 'Approved'";
    
        // Apply status filter based on the value of $statusFilter
        if ($statusFilter === 'CompletedPaid') {
            // If 'CompletedPaid' is selected, filter by 'Completed' and 'Paid'
            $query .= " AND (a.status = 'Completed' AND a.p_status = 'Paid')";
        } else {
            // Default filter for 'Process' or 'Unpaid'
            $query .= " AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
        }
    
        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
        
        // Check if the query execution is successful
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // Fetch and return all appointment records as an associative array
                $appointments = $result->fetch_all(MYSQLI_ASSOC);
                return $appointments;
            } else {
                // Return an empty array if no records found
                return [];
            }
        } else {
            // Return null in case of a query execution error
            return null;
        }
    }
    
    public function getRequestSchedule() {
        // Prepare SQL query with INNER JOIN and LEFT JOIN on three tables
        $query = "
            SELECT 
            pa.pr_status AS approve_priest,
                s.schedule_id, 
                s.citizen_id, 
                s.date, 
                s.start_time, 
                s.end_time, 
                s.event_type, 
                r.req_id, 
                r.req_name_pamisahan, 
                r.req_address, 
                r.req_category, 
                r.req_person, 
                r.req_pnumber, 
                r.cal_date, 
                r.req_chapel, 
                r.status AS req_status, 
                r.role, 
                r.created_at, 
                priest.fullname AS priest_name,
                pa.pr_status
                
              
                FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
    
        JOIN 
           req_form r ON s.schedule_id = r.schedule_id
           LEFT JOIN 
           priest_approval pa ON pa.approval_id = r.approval_id
       LEFT JOIN 
           citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
           WHERE 
         r.status = 'Pending'";
    
        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
    
        // Execute the query
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // Fetch all appointment records
                $appointments = $result->fetch_all(MYSQLI_ASSOC);
                return $appointments;
            } else {
                return []; // Return empty array if no appointments found
            }
        } else {
            // Handle query execution error
            return null;
        }
    }
    
    

    public function deleteMassWedding($massweddingffill_id) {
        // Step 1: Retrieve citizen data and event details
        $sql = "
        SELECT 
            c.citizend_id, 
            c.fullname,
            c.email, 
            c.phone, 
            mf.event_name,
            mf.announcement_id  -- assuming marriagefill has an announcement_id for reference
        FROM 
            marriagefill mf
        LEFT JOIN 
            citizen c ON c.citizend_id = mf.citizen_id 
        WHERE 
            mf.marriagefill_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error.']);
        }
    
        $stmt->bind_param("i", $massweddingffill_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            // Step 2: Retrieve contact and announcement info
            $contactInfo = $result->fetch_assoc();
            $email = $contactInfo['email'];
            $citizen_name = $contactInfo['fullname'];
            $title = $contactInfo['event_name'];
            $announcementId = $contactInfo['announcement_id']; // Store related announcement ID
        } else {
            return json_encode(['success' => false, 'message' => 'No contact info found.']);
        }
    
        // Step 3: Delete the wedding entry
        $deleteSql = "DELETE FROM marriagefill WHERE marriagefill_id = ?";
        $deleteStmt = $this->conn->prepare($deleteSql);
        if (!$deleteStmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error during delete.']);
        }
    
        $deleteStmt->bind_param("i", $massweddingffill_id);
    
        if ($deleteStmt->execute()) {
            // Step 4: Update announcement capacity after deletion
            $updateCapacitySql = "
            UPDATE announcement
            SET capacity = capacity + 1  -- Increment capacity by 1
            WHERE announcement_id = ?";
            
            $updateStmt = $this->conn->prepare($updateCapacitySql);
            if (!$updateStmt) {
                return json_encode(['success' => false, 'message' => 'SQL prepare error during capacity update.']);
            }
            
            $updateStmt->bind_param("i", $announcementId);
            $updateStmt->execute();
    
            // Step 5: Send email notification if citizen has an email
            $emailSent = $this->sendDeclineEmail($email, $citizen_name, "Your Appointment has been deleted.",
            "Dear {$citizen_name},<br><br>Your {$title} Appointment has been deleted due to unavailable capacity.<br>If you have any questions, please contact us.<br>");
            
            return $emailSent ? true : json_encode(['success' => false, 'message' => 'Email notification failed.']);
        } else {
            return json_encode(['success' => false, 'message' => 'Failed to delete the wedding.']);
        }
    }
    
    public function deleteMassBaptism($massbaptismfillId) {
        // Step 1: Fetch citizen data and event details
        $sql = "
        SELECT 
            c.citizend_id, 
            c.fullname, 
            c.email, 
            c.phone, 
            b.event_name,
            b.announcement_id  -- assuming baptismfill table has announcement_id for reference
        FROM 
            baptismfill b 
        LEFT JOIN 
            citizen c ON c.citizend_id = b.citizen_id 
        WHERE 
            b.baptism_id = ?";
    
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error.']);
        }
    
        $stmt->bind_param("i", $massbaptismfillId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            // Step 2: Retrieve contact and announcement info
            $contactInfo = $result->fetch_assoc();
            $email = $contactInfo['email'] ?? null;
            $citizen_name = $contactInfo['fullname'] ?? 'Walk-in Client';
            $title = $contactInfo['event_name'];
            $announcementId = $contactInfo['announcement_id']; // Store related announcement ID
        } else {
            return json_encode(['success' => false, 'message' => 'No contact info found.']);
        }
    
        // Step 3: Delete the baptism record
        $deleteSql = "DELETE FROM baptismfill WHERE baptism_id = ?";
        $deleteStmt = $this->conn->prepare($deleteSql);
        if (!$deleteStmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error during delete.']);
        }
    
        $deleteStmt->bind_param("i", $massbaptismfillId);
        
        if ($deleteStmt->execute()) {
            // Step 4: Update announcement capacity after deletion
            $updateCapacitySql = "
            UPDATE announcement
            SET capacity = capacity + 1  -- Increment capacity by 1
            WHERE announcement_id = ?";
            
            $updateStmt = $this->conn->prepare($updateCapacitySql);
            if (!$updateStmt) {
                return json_encode(['success' => false, 'message' => 'SQL prepare error during capacity update.']);
            }
            
            $updateStmt->bind_param("i", $announcementId);
            $updateStmt->execute();
            
            // Step 5: Send email notification if citizen has an email
            if ($email) {
                $emailSent = $this->sendDeclineEmail($email, $citizen_name, "Your Appointment has been deleted.",
                "Dear {$citizen_name},<br><br>Your {$title} Appointment has been deleted due to unavailable capacity or incorrect information.<br>If you have any questions, please contact us.<br>");
                
                return $emailSent ? true : json_encode(['success' => false, 'message' => 'Email notification failed.']);
            } else {
                return json_encode(['success' => true, 'message' => 'Walk-in appointment declined. No email sent.']);
            }
        } else {
            return json_encode(['success' => false, 'message' => 'Failed to delete the baptism.']);
        }
    }
    
    
    public function deleteDefuctom($defuctom_id) {
        // Combine logic to retrieve contact info and title
        $sql = "
        SELECT 
        c.citizend_id, 
        c.fullname,
        c.email, 
        c.phone, 
        df.event_name 
    FROM 
    schedule s
       LEFT JOIN 
        citizen c  ON c.citizend_id = s.citizen_id 
        JOIN 
        defuctomfill df ON s.schedule_id = df.schedule_id 
    WHERE 
        df.defuctomfill_id = ?"; // Ensure this column name matches your schema
    
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error.']);
        }
    
        $stmt->bind_param("i", $defuctom_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            // Retrieve contact info
            $contactInfo = $result->fetch_assoc();
            $email = $contactInfo['email'];
            $citizen_name = $contactInfo['fullname'];
            $title = $contactInfo['event_name'];
        } else {
            return json_encode(['success' => false, 'message' => 'No contact info found.']);
        }
    
        // Prepare SQL to delete baptism and associated schedule
        $deleteSql = "DELETE df, s 
        FROM defuctomfill df 
        INNER JOIN schedule s ON df.schedule_id = s.schedule_id 
        WHERE df.defuctomfill_id = ?"; // Ensure this field matches your schema
    
        $deleteStmt = $this->conn->prepare($deleteSql);
        if (!$deleteStmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error during delete.']);
        }
    
        $deleteStmt->bind_param("i", $defuctom_id);
    
        if ($deleteStmt->execute()) {
            // Send decline email after deletion
            $emailSent = $this->sendDeclineEmail($email, $citizen_name, "Your Appointment has been deleted.",
            "Dear {$citizen_name},<br><br>Your $title Appointment has been deleted due to the lack of a Priest or seminar availability.<br>If you have any questions, please contact us.<br>");
    
            return $emailSent ? true : json_encode(['success' => false, 'message' => 'Email notification failed.']);
        } else {
            return json_encode(['success' => false, 'message' => 'Failed to delete the baptism.']);
        }
    }
    public function deleteWedding($weddingffill_id) {
        // Combine logic to retrieve contact info and title
        $sql = "
        SELECT 
        c.citizend_id, 
        c.fullname,
        c.email, 
        c.phone, 
        mf.event_name 
    FROM 
    schedule s
      LEFT JOIN 
        citizen c ON c.citizend_id = s.citizen_id 
        JOIN 
        marriagefill mf ON s.schedule_id = mf.schedule_id 
    WHERE 
        mf.marriagefill_id = ?"; // Ensure this column name matches your schema
    
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error.']);
        }
    
        $stmt->bind_param("i", $weddingffill_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            // Retrieve contact info
            $contactInfo = $result->fetch_assoc();
            $email = $contactInfo['email'];
            $citizen_name = $contactInfo['fullname'];
            $title = $contactInfo['event_name'];
        } else {
            return json_encode(['success' => false, 'message' => 'No contact info found.']);
        }
    
        // Prepare SQL to delete baptism and associated schedule
        $deleteSql = "DELETE mf, s 
        FROM marriagefill mf 
        INNER JOIN schedule s ON mf.schedule_id = s.schedule_id 
        WHERE mf.marriagefill_id = ?"; // Ensure this field matches your schema
    
        $deleteStmt = $this->conn->prepare($deleteSql);
        if (!$deleteStmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error during delete.']);
        }
    
        $deleteStmt->bind_param("i", $weddingffill_id);
    
        if ($deleteStmt->execute()) {
            // Send decline email after deletion
            $emailSent = $this->sendDeclineEmail($email, $citizen_name, "Your Appointment has been deleted.",
            "Dear {$citizen_name},<br><br>Your $title Appointment has been deleted due to the lack of a Priest or seminar availability.<br>If you have any questions, please contact us.<br>");
    
            return $emailSent ? true : json_encode(['success' => false, 'message' => 'Email notification failed.']);
        } else {
            return json_encode(['success' => false, 'message' => 'Failed to delete the baptism.']);
        }
    }
    public function deleteMassConfirmation($confirmationfill_idss) {
        // Step 1: Retrieve citizen data and event details
        $sql = "
        SELECT 
            c.citizend_id, 
            c.fullname,
            c.email, 
            c.phone, 
            cf.event_name,
            cf.announcement_id  -- assuming confirmationfill has an announcement_id for reference
        FROM 
            confirmationfill cf
        LEFT JOIN 
            citizen c ON c.citizend_id = cf.citizen_id 
        WHERE 
            cf.confirmationfill_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error.']);
        }
    
        $stmt->bind_param("i", $confirmationfill_idss);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            // Step 2: Retrieve contact and announcement info
            $contactInfo = $result->fetch_assoc();
            $email = $contactInfo['email'];
            $citizen_name = $contactInfo['fullname'];
            $title = $contactInfo['event_name'];
            $announcementId = $contactInfo['announcement_id']; // Store related announcement ID
        } else {
            return json_encode(['success' => false, 'message' => 'No contact info found.']);
        }
    
        // Step 3: Delete the confirmation entry
        $deleteSql = "DELETE FROM confirmationfill WHERE confirmationfill_id = ?";
        $deleteStmt = $this->conn->prepare($deleteSql);
        if (!$deleteStmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error during delete.']);
        }
    
        $deleteStmt->bind_param("i", $confirmationfill_idss);
    
        if ($deleteStmt->execute()) {
            // Step 4: Update announcement capacity after deletion
            $updateCapacitySql = "
            UPDATE announcement
            SET capacity = capacity + 1  -- Increment capacity by 1
            WHERE announcement_id = ?";
            
            $updateStmt = $this->conn->prepare($updateCapacitySql);
            if (!$updateStmt) {
                return json_encode(['success' => false, 'message' => 'SQL prepare error during capacity update.']);
            }
            
            $updateStmt->bind_param("i", $announcementId);
            $updateStmt->execute();
    
            // Step 5: Send email notification if citizen has an email
            $emailSent = $this->sendDeclineEmail($email, $citizen_name, "Your Appointment has been deleted.",
            "Dear {$citizen_name},<br><br>Your {$title} Appointment has been deleted due to unavailable capacity.<br>If you have any questions, please contact us.<br>");
            
            return $emailSent ? true : json_encode(['success' => false, 'message' => 'Email notification failed.']);
        } else {
            return json_encode(['success' => false, 'message' => 'Failed to delete the confirmation.']);
        }
    }
    
    public function deleteConfirmation($confirmationfill_id) {
        // Combine logic to retrieve contact info and title
        $sql = "
        SELECT 
        c.citizend_id, 
        c.fullname,
        c.email, 
        c.phone, 
        cf.event_name 
    FROM 
      
        confirmationfill cf
    LEFT JOIN 
    citizen c  ON c.citizend_id = cf.citizen_id 
    WHERE 
        cf.confirmationfill_id = ?"; // Ensure this column name matches your schema
    
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error.']);
        }
    
        $stmt->bind_param("i", $confirmationfill_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            // Retrieve contact info
            $contactInfo = $result->fetch_assoc();
            $email = $contactInfo['email'];
            $citizen_name = $contactInfo['fullname'];
            $title = $contactInfo['event_name'];
        } else {
            return json_encode(['success' => false, 'message' => 'No contact info found.']);
        }
    
        // Prepare SQL to delete baptism and associated schedule
        $deleteSql = "DELETE cf
                      FROM confirmationfill cf 
                      WHERE cf.confirmationfill_id = ?"; // Ensure this field matches your schema
    
        $deleteStmt = $this->conn->prepare($deleteSql);
        if (!$deleteStmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error during delete.']);
        }
    
        $deleteStmt->bind_param("i", $confirmationfill_id);
    
        if ($deleteStmt->execute()) {
            // Send decline email after deletion
            $emailSent = $this->sendDeclineEmail($email, $citizen_name, "Your Appointment has been deleted.",
            "Dear {$citizen_name},<br><br>Your $title Appointment has been deleted due to unavailable capacity.<br>If you have any questions, please contact us.<br>");
    
            return $emailSent ? true : json_encode(['success' => false, 'message' => 'Email notification failed.']);
        } else {
            return json_encode(['success' => false, 'message' => 'Failed to delete the baptism.']);
        }
    }
    
    public function deleteBaptism($baptismfill_id) {
        // Combine logic to retrieve contact info and title
        $sql = "
            SELECT 
                c.citizend_id, 
                c.fullname,
                c.email, 
                c.phone, 
                b.event_name 
            FROM 
            schedule s
          LEFT  JOIN 
            citizen c ON c.citizend_id = s.citizen_id 
            JOIN 
                baptismfill b ON s.schedule_id = b.schedule_id 
            WHERE 
                b.baptism_id = ?"; // Ensure this column name matches your schema
    
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error.']);
        }
    
        $stmt->bind_param("i", $baptismfill_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            // Retrieve contact info
            $contactInfo = $result->fetch_assoc();
            $email = $contactInfo['email'];
            $citizen_name = $contactInfo['fullname'];
            $title = $contactInfo['event_name'];
        } else {
            return json_encode(['success' => false, 'message' => 'No contact info found.']);
        }
    
        // Prepare SQL to delete baptism and associated schedule
        $deleteSql = "DELETE bf, s 
                      FROM baptismfill bf 
                      INNER JOIN schedule s ON bf.schedule_id = s.schedule_id 
                      WHERE bf.baptism_id = ?"; // Ensure this field matches your schema
    
        $deleteStmt = $this->conn->prepare($deleteSql);
        if (!$deleteStmt) {
            return json_encode(['success' => false, 'message' => 'SQL prepare error during delete.']);
        }
    
        $deleteStmt->bind_param("i", $baptismfill_id);
    
        if ($deleteStmt->execute()) {
            // Send decline email after deletion
            $emailSent = $this->sendDeclineEmail($email, $citizen_name, "Your Appointment has been deleted.",
            "Dear {$citizen_name},<br><br>Your $title Appointment has been deleted due to the lack of a Priest or seminar availability.<br>If you have any questions, please contact us.<br>");
    
            return $emailSent ? true : json_encode(['success' => false, 'message' => 'Email notification failed.']);
        } else {
            return json_encode(['success' => false, 'message' => 'Failed to delete the baptism.']);
        }
    }
    
    
    public function sendDeclineEmail($email, $citizen_name, $subject, $body) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "argaoparishchurch@gmail.com";
            $mail->Password = "xomoabhlnrlzenur";
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
    
            $mail->setFrom('argaoparishchurch@gmail.com');
            $mail->addAddress($email);
            $mail->addEmbeddedImage('../Controller/signature.png', 'signature_img');
            $mail->addEmbeddedImage('../Controller/logo.jpg', 'background_img');
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = "<div style='width: 100%; max-width: 400px; margin: auto; background: url(cid:background_img) no-repeat center center; background-size: cover; padding: 20px;'>
                           <div style='background: rgba(255, 255, 255, 0.8); padding: 20px;width:100%;height:auto;'>
                               {$body}
                               <img src='cid:signature_img' style='width: 200px; height: auto;'>
                           </div>
                       </div>";
    
            if (!$mail->send()) {
                error_log("Email failed: " . $mail->ErrorInfo); // Log error
                return false; // Indicate failure
            }
            return true; // Indicate success
        } catch (Exception $e) {
            error_log("Error sending email notification: " . $e->getMessage()); // Log error
            return false; // Indicate failure
        }
    }
    
   
    
    public function getContactInfoAndTitle($baptismfillId = null, $massbaptismfillId = null) {
        $sql = "";
        $id = null;
    
        if ($massbaptismfillId) {
            // Use mass baptism fill ID
            $sql = "
                SELECT 
                    c.citizend_id, 
                    c.fullname,
                    c.email, 
                    c.phone, 
                    b.event_name 
                FROM 
                    citizen c 
                JOIN 
                    baptismfill b ON c.citizend_id = b.citizen_id  
                WHERE 
                    b.baptism_id = ?";
            $id = $massbaptismfillId;
        } elseif ($baptismfillId) {
            // Use baptism fill ID
            $sql = "
                SELECT 
                    c.citizend_id, 
                    c.fullname,
                    c.email, 
                    c.phone, 
                    b.event_name 
                FROM 
                    citizen c 
                JOIN 
                    schedule s ON c.citizend_id = s.citizen_id 
                JOIN 
                    baptismfill b ON s.schedule_id = b.schedule_id 
                WHERE 
                    b.baptism_id = ?";
            $id = $baptismfillId;
        }
    
        if (!$id) {
            return false; // If no ID is provided, return false
        }
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Returns an associative array with email, phone, and event_name
        } else {
            return false; // Returns false if no contact info is found
        }
    }
    
   

    
    public function updatePaymentStatus($appsched_id, $p_status) {
        // Set the timezone to the Philippines
        date_default_timezone_set('Asia/Manila');
    
        // Get the current date and time
        $paid_date = date('Y-m-d H:i:s');
        
        // Update the SQL statement to include the paid_date
        $sql = "UPDATE appointment_schedule SET p_status = ?, paid_date = ? WHERE appsched_id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt) {
            // Bind the parameters: p_status, paid_date, and appsched_id
            $stmt->bind_param('ssi', $p_status, $paid_date, $appsched_id);
            return $stmt->execute();
        }
        return false;
    }
    
    
    // Method to update event status
    public function updateEventStatus($cappsched_id, $c_status) {
        $sql = "UPDATE appointment_schedule SET status = ? WHERE appsched_id = ?";
        $stmt = $this->conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('si', $c_status, $cappsched_id);
            return $stmt->execute();
        }
        return false;
    }
    public function deleteAppointments($appsched_ids) { 
        // Generate placeholders for the prepared statement
        $placeholders = implode(',', array_fill(0, count($appsched_ids), '?'));
        $types = str_repeat('i', count($appsched_ids));
    
        // Step 1: Delete from the `schedule` table based on conditions
        $deleteScheduleSql = "
            DELETE FROM schedule 
            WHERE schedule_id IN (
                -- Based on baptismfill
                SELECT schedule_id 
                FROM baptismfill 
                WHERE baptism_id IN (
                    SELECT baptismfill_id 
                    FROM appointment_schedule 
                    WHERE appsched_id IN ($placeholders)
                )
            )
            OR schedule_id IN (
                -- Based on appointment_schedule directly
                SELECT schedule_id 
                FROM appointment_schedule 
                WHERE appsched_id IN ($placeholders)
            )
            OR schedule_id IN (
                -- Based on marriagefill
                SELECT schedule_id 
                FROM marriagefill 
                WHERE marriagefill_id IN (
                    SELECT marriage_id 
                    FROM appointment_schedule 
                    WHERE appsched_id IN ($placeholders)
                )
            )
            OR schedule_id IN (
                -- Based on defuctomfill
                SELECT schedule_id 
                FROM defuctomfill 
                WHERE defuctomfill_id IN (
                    SELECT defuctom_id 
                    FROM appointment_schedule 
                    WHERE appsched_id IN ($placeholders)
                )
                
            )
            OR schedule_id IN (
                -- Based on req_form
                SELECT schedule_id 
                FROM req_form 
                WHERE req_id IN (
                    SELECT request_id 
                    FROM appointment_schedule 
                    WHERE appsched_id IN ($placeholders)
                )
            )
            ";
        
        // Prepare the SQL statement for schedule deletion
        $stmtSchedule = $this->conn->prepare($deleteScheduleSql);
        
        if ($stmtSchedule) {
            // Bind the parameters for each of the sets of placeholders
            $stmtSchedule->bind_param($types .$types . $types . $types . $types, 
                ...array_merge($appsched_ids,$appsched_ids, $appsched_ids, $appsched_ids, $appsched_ids)
            );
            
            // Execute the deletion for schedules and check for errors
            if (!$stmtSchedule->execute()) {
                echo "Error deleting from schedule: " . $stmtSchedule->error;
                return false; // Exit if deletion fails
            }
        } else {
            echo "Error preparing SQL for schedule deletion: " . $this->conn->error;
            return false; // Exit if SQL preparation fails
        }
    
        // Step 2: Delete from `baptismfill`
        $deleteBaptismFillSql = "
            DELETE FROM baptismfill 
            WHERE baptism_id IN (
                SELECT baptismfill_id 
                FROM appointment_schedule 
                WHERE appsched_id IN ($placeholders)
            )";
        
        $stmtBaptismFill = $this->conn->prepare($deleteBaptismFillSql);
        
        if ($stmtBaptismFill) {
            // Bind the parameters for baptismfill deletion
            $stmtBaptismFill->bind_param($types, ...$appsched_ids);
            
            // Execute the deletion for baptismfill and check for errors
            if (!$stmtBaptismFill->execute()) {
                echo "Error deleting from baptismfill: " . $stmtBaptismFill->error;
                return false; // Exit if deletion fails
            }
        } else {
            echo "Error preparing SQL for baptismfill deletion: " . $this->conn->error;
            return false; // Exit if SQL preparation fails
        }
    
        // Step 3: Delete from `marriagefill`
        $deleteMarriageFillSql = "
            DELETE FROM marriagefill 
            WHERE marriagefill_id IN (
                SELECT marriage_id 
                FROM appointment_schedule 
                WHERE appsched_id IN ($placeholders)
            )";
        
        $stmtMarriageFill = $this->conn->prepare($deleteMarriageFillSql);
        
        if ($stmtMarriageFill) {
            // Bind the parameters for marriagefill deletion
            $stmtMarriageFill->bind_param($types, ...$appsched_ids);
            
            // Execute the deletion for marriagefill and check for errors
            if (!$stmtMarriageFill->execute()) {
                echo "Error deleting from marriagefill: " . $stmtMarriageFill->error;
                return false; // Exit if deletion fails
            }
        } else {
            echo "Error preparing SQL for marriagefill deletion: " . $this->conn->error;
            return false; // Exit if SQL preparation fails
        }
    
        // Step 4: Delete from `confirmationfill`
        $deleteConfirmationFillSql = "
            DELETE FROM confirmationfill 
            WHERE confirmationfill_id IN (
                SELECT confirmation_id 
                FROM appointment_schedule 
                WHERE appsched_id IN ($placeholders)
            )";
        
        $stmtConfirmationFill = $this->conn->prepare($deleteConfirmationFillSql);
        
        if ($stmtConfirmationFill) {
            // Bind the parameters for confirmationfill deletion
            $stmtConfirmationFill->bind_param($types, ...$appsched_ids);
            
            // Execute the deletion for confirmationfill and check for errors
            if (!$stmtConfirmationFill->execute()) {
                echo "Error deleting from confirmationfill: " . $stmtConfirmationFill->error;
                return false; // Exit if deletion fails
            }
        } else {
            echo "Error preparing SQL for confirmationfill deletion: " . $this->conn->error;
            return false; // Exit if SQL preparation fails
        }
    
        // Step 5: Delete from appointment_schedule itself after all related records are deleted
        $deleteAppointmentSql = "
            DELETE FROM appointment_schedule 
            WHERE appsched_id IN ($placeholders)";
        
        // Prepare the SQL statement for appointment deletion
        $stmtAppointment = $this->conn->prepare($deleteAppointmentSql);
        
        if ($stmtAppointment) {
            // Bind the parameters for appointment_schedule
            $stmtAppointment->bind_param($types, ...$appsched_ids);
            
            // Execute the deletion for appointment_schedule and check for errors
            if (!$stmtAppointment->execute()) {
                echo "Error deleting from appointment_schedule: " . $stmtAppointment->error;
                return false; // Exit if deletion fails
            }
        } else {
            echo "Error preparing SQL for appointment_schedule deletion: " . $this->conn->error;
            return false; // Exit if SQL preparation fails
        }
    
        // If everything went well, return true to indicate success
        return true;
    }
    
    
    
    
   // Method to get the schedule_id from baptismfill

   public function getwScheduleId($weddingffill_id) {
    $sql = "SELECT `schedule_id` FROM `marriagefill` WHERE `marriagefill_id` = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('i', $weddingffill_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['schedule_id'];
    } else {
        return null;
    }
}

   public function getdScheduleId($defuctom_id) {
    $sql = "SELECT `schedule_id` FROM `defuctomfill` WHERE `defuctomfill_id` = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('i', $defuctom_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['schedule_id'];
    } else {
        return null;
    }
}
public function getScheduleDetails($baptismfill_id = null, $confirmationfill_id = null, $defuctom_id = null, $weddingffill_id = null, $announcement_id = null,$request_id =null) {
    $query = "
        SELECT s.date AS schedule_date, s.start_time AS schedule_start_time, s.end_time AS schedule_end_time 
        FROM baptismfill b
        JOIN schedule s ON b.schedule_id = s.schedule_id
        WHERE b.baptism_id = ?

        UNION
        SELECT s.date AS schedule_date, s.start_time AS schedule_start_time, s.end_time AS schedule_end_time 
        FROM confirmationfill cf
        JOIN schedule s ON cf.schedule_id = s.schedule_id
        WHERE cf.confirmationfill_id = ? 

        UNION
        SELECT s.date AS schedule_date, s.start_time AS schedule_start_time, s.end_time AS schedule_end_time 
        FROM defuctomfill df
        JOIN schedule s ON df.schedule_id = s.schedule_id
        WHERE df.defuctomfill_id = ? 

        UNION
        SELECT s.date AS schedule_date, s.start_time AS schedule_start_time, s.end_time AS schedule_end_time 
        FROM marriagefill mf
        JOIN schedule s ON mf.schedule_id = s.schedule_id
        WHERE mf.marriagefill_id = ? 

        UNION
        SELECT s.date AS schedule_date, s.start_time AS schedule_start_time, s.end_time AS schedule_end_time 
        FROM announcement a
        JOIN schedule s ON a.schedule_id = s.schedule_id
        WHERE a.announcement_id = ?
        UNION
        SELECT s.date AS schedule_date, s.start_time AS schedule_start_time, s.end_time AS schedule_end_time 
        FROM req_form rf
        JOIN schedule s ON rf.schedule_id = s.schedule_id
        WHERE rf.req_id = ? 
        
    ";

    $stmt = $this->conn->prepare($query);

    // Bind the parameters correctly (there are 5 now, not 4)
    $stmt->bind_param("iiiiii", $baptismfill_id, $confirmationfill_id, $defuctom_id, $weddingffill_id, $announcement_id,$request_id);
    
    $stmt->execute();

    // Return the results
    return $stmt->get_result()->fetch_assoc();
}

public function getScheduleId($baptismfill_id) {
    $sql = "SELECT `schedule_id` FROM `baptismfill` WHERE `baptism_id` = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('i', $baptismfill_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['schedule_id'];
    } else {
        return null;
    }
}

public function getcScheduleId($confirmationfill_id) {
 $sql = "SELECT `schedule_id` FROM `confirmationfill` WHERE `confirmationfill_id` = ?";
 $stmt = $this->conn->prepare($sql);
 $stmt->bind_param('i', $confirmationfill_id);
 $stmt->execute();
 $result = $stmt->get_result();

 if ($result->num_rows > 0) {
     $row = $result->fetch_assoc();
     return $row['schedule_id'];
 } else {
     return null;
 }
}

//--------------------------------------------------------------------------------------------------------
public function getScheduleDays($startDate, $endDate) {
    $scheduleDays = [];
    $currentDate = $startDate;

    while ($currentDate <= $endDate) {
        $dayOfMonth = date('j', strtotime($currentDate)); // Get the day of the month
        $dayOfWeek = date('N', strtotime($currentDate)); // Get the day of the week (1 = Monday, 7 = Sunday)

        // Check for 2nd week (8th to 14th) or 4th Saturday of the month
        if (($dayOfMonth >= 8 && $dayOfMonth <= 14 && $dayOfWeek == 6) || // 2nd week Saturday
            (date('l', strtotime($currentDate)) == 'Saturday' && ceil($dayOfMonth / 7) == 4)) { // 4th Saturday
            $scheduleDays[] = $currentDate;
        }

        // Move to the next day
        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
    }

    return $scheduleDays;
}
public function displaySundaysDropdowns($schedule_id) {
    // Fetch the schedule date based on the schedule_id
    $sql = "SELECT date FROM schedule WHERE schedule_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('i', $schedule_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $schedule_date = $row['date'];
        
        // Get Sundays between today and the schedule date
        $sundays = $this->getScheduleDays(date('Y-m-d'), $schedule_date);
        
        // Define the fixed start and end times
        $start_time = "08:00 AM";
        $end_time = "5:00 PM";

        foreach ($sundays as $sunday) {
            // Combine values in the option for easier form processing later
            $option_value = "{$schedule_id}|{$sunday}|{$start_time}|{$end_time}";

            // Display the date with the fixed time range
            echo "<option value='{$option_value}'>{$sunday} - {$start_time} to {$end_time}</option>";
        }
    } else {
        echo "<option>No available schedules found.</option>";
    }
}
//-----------------------------------------------------------------------------------------------
// Method to get Sundays between start date and schedule date
public function getSundays($startDate, $endDate) {
    $sundays = [];
    $currentDate = $startDate;

    // Skip today if today is Sunday
    if (date('N', strtotime($currentDate)) == 7) {
        // If today is Sunday, start from the next day
        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
    }

    // Continue finding Sundays between start date and end date
    while ($currentDate <= $endDate) {
        if (date('N', strtotime($currentDate)) == 7) { // Check if it's Sunday
            $sundays[] = $currentDate;
        }
        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
    }

    return $sundays;
}


public function displaySundaysDropdown($schedule_id) {
    // Fetch the schedule date based on the schedule_id
    $sql = "SELECT date FROM schedule WHERE schedule_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param('i', $schedule_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $schedule_date = $row['date'];
        
        // Get Sundays between today and the schedule date
        $sundays = $this->getSundays(date('Y-m-d'), $schedule_date);
        
        // Define the fixed start and end times
        $start_time = "09:00 AM";
        $end_time = "11:00 AM";

        foreach ($sundays as $sunday) {
            // Combine values in the option for easier form processing later
            $option_value = "{$schedule_id}|{$sunday}|{$start_time}|{$end_time}";

            // Display the date with the fixed time range
            echo "<option value='{$option_value}'>{$sunday} - {$start_time} to {$end_time}</option>";
        }
    } else {
        echo "<option>No available schedules found.</option>";
    }
}



  
    
    
    public function getAnnouncementById($announcementId) {
        $sql = "SELECT 
                    `announcement`.`announcement_id`,
                    `announcement`.`event_type`,
                    `announcement`.`title`,
                    `announcement`.`description`,
                    `announcement`.`date_created`,
                    `announcement`.`capacity`,
                    `schedule`.`date`,
                    `schedule`.`start_time`,
                    `schedule`.`end_time`
                FROM 
                    `announcement`
                JOIN 
                    `schedule` ON `announcement`.`schedule_id` = `schedule`.`schedule_id`
                WHERE
                    `announcement`.`announcement_id` = ?
                LIMIT 1";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $announcementId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }
    public function insertEventCalendar($cal_fullname, $cal_Category, $cal_date, $cal_description) {
        $sql = "INSERT INTO event_calendar (cal_fullname, cal_Category, cal_date, cal_description) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $cal_fullname, $cal_Category, $cal_date, $cal_description);
        
        // Return true if execution is successful, otherwise false
        return $stmt->execute();
    }
    
    public function getAnnouncements() {
        $sql = "
        SELECT 
        a.speaker_ann,
        a.announcement_id,
        a.event_type AS event_type,  
      
        a.seminar_id, 
        a.event_type AS announcement_event_type, 
        a.title, 
        a.description, 
        a.schedule_id AS announcement_schedule_id, 
        a.date_created, 
        a.capacity, 
        a.pending_capacity,
        priest.fullname AS fullname,
        s1.date AS schedule_date, 
        s1.start_time AS schedule_start_time, 
        s1.end_time AS schedule_end_time, 
       
      
        s2.date AS seminar_date, 
        s2.start_time AS seminar_start_time, 
        s2.end_time AS seminar_end_time, 
      
        pa.approval_id,          -- From priest_approval
        pa.priest_id AS approval_priest_id,  -- Priest from priest_approval
        pa.pr_status,            -- Priest status from priest_approval
        pa.assigned_time         -- Assigned time from priest_approval
    FROM 
        announcement AS a
    LEFT JOIN 
        schedule s1 ON a.schedule_id = s1.schedule_id  -- Joining for regular schedule
    LEFT JOIN 
        schedule s2 ON a.seminar_id = s2.schedule_id   -- Joining for seminar schedule

    LEFT JOIN 
        priest_approval pa ON pa.approval_id = a.approval_id
    LEFT JOIN 
        citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active' -- Join based on citizen_id
    ORDER BY 
        a.date_created DESC";  // Corrected to use alias 'a'
    
        $result = $this->conn->query($sql);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        $announcements = [];
        while ($row = $result->fetch_assoc()) {
            $announcements[] = $row;
        }
        return $announcements;
    }
    
    
    public function updateAnnouncement($announcementId, $speakerAnn, $title, $description, $capacity) {
        $sql = "UPDATE announcement SET speaker_ann = ?, title = ?, description = ?, capacity = ? WHERE announcement_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssi", $speakerAnn, $title, $description, $capacity, $announcementId);
        
        return $stmt->execute();
    }
    public function deleteAnnouncement($announcementId) {
        $sql = "DELETE FROM announcement WHERE announcement_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $announcementId);
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Delete Error: " . $stmt->error); // Logs error to server log
            return false;
        }
    }
    
//------------------------------------------------------------------------------------//
// In Staff class

public function insertSchedule($date, $startTime, $endTime, $eventType) {
    $sql = "INSERT INTO schedule (date, start_time, end_time, event_type) VALUES (?, ?, ?, 'Seminar')";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("sss", $date, $startTime, $endTime, );

    if ($stmt->execute()) {
        $insertedScheduleId = $stmt->insert_id;
        $stmt->close();
        return $insertedScheduleId;
    } else {
        error_log("Schedule insertion failed: " . $stmt->error);
        $stmt->close();
        return false; // Insertion failed
    }
}
public function insertIntoWalkinBaptismFill($scheduleId, $fatherFullname, $fullname, $gender, $c_date_birth, $address, $pbirth, $mother_fullname, $religion, $parentResident, $godparent, $age) {
    $sql = "INSERT INTO baptismfill (schedule_id, father_fullname, fullname, gender, c_date_birth, address, pbirth, mother_fullname, religion, parent_resident, godparent, age, status, event_name, role, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Baptism', 'Walk', CURRENT_TIMESTAMP)";
    
    $stmt = $this->conn->prepare($sql);
    
    // Bind parameters
    $stmt->bind_param("issssssssssi", $scheduleId, $fatherFullname, $fullname, $gender, $c_date_birth, $address, $pbirth, $mother_fullname, $religion, $parentResident, $godparent, $age);

    if ($stmt->execute()) {
        // Return the last inserted ID
        $baptismfillId = $this->conn->insert_id;
        $stmt->close();
        return $baptismfillId;
    } else {
        error_log("Insert failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

public function insertMassAppointment($massbaptismfillId = null ,$massweddingffill_id = null , $payableAmount ) {
    // Generate a random 12-letter reference number
    $referenceNumber = $this->generateReferenceNumber();

    // Update the SQL to match the parameters correctly
    $sql = "INSERT INTO appointment_schedule (baptismfill_id,marriage_id, payable_amount,  status, p_status,reference_number)
            VALUES (?, ?,?,'Process','Unpaid',?)";
    $stmt = $this->conn->prepare($sql);
    
    // Adjust the bind_param to include the reference number
    $stmt->bind_param("iids", $massbaptismfillId, $massweddingffill_id,$payableAmount, $referenceNumber);

    if ($stmt->execute()) {
        // Get the last inserted ID
        $appointmentId = $this->conn->insert_id;
        $stmt->close();
        return $appointmentId;  // Return the ID of the newly inserted record
    } else {
        error_log("Insertion failed: " . $stmt->error);
        $stmt->close();
        return false;  // Insertion failed
    }
}


private static $generatedReferences = [];

private function generateReferenceNumber() {
    do {
        // Generate a random string of 12 uppercase letters and numbers
        $referenceNumber = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), 0, 4);
    } while (in_array($referenceNumber, self::$generatedReferences));

    // Store the generated reference number to avoid future duplicates
    self::$generatedReferences[] = $referenceNumber;

    return $referenceNumber;
}


public function approveBaptism($baptismfillId = null, $massbaptismfillId = null) {
    try {
        if ($baptismfillId !== null) {
            $sql = "UPDATE baptismfill SET status = 'Approved' WHERE baptism_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $baptismfillId);
        } elseif ($massbaptismfillId !== null) {
            $sql = "UPDATE baptismfill SET status = 'Approved' WHERE baptism_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $massbaptismfillId);
        } else {
            return false;  // Neither ID was provided
        }
        
        if ($stmt->execute()) {
            return true;  // Status updated successfully
        } else {
            return false;  // Failed to update status
        }
    } catch (Exception $e) {
        error_log("Error approving baptism or mass baptism: " . $e->getMessage());
        return false;  // Error occurred
    }
}


public function insertBaptismPayment($appointmentId, $payableAmount) {
    try {
        $sql = "INSERT INTO payments (appointment_id, amount)
                VALUES (?, ?)";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("id", $appointmentId, $payableAmount);
        if ($stmt->error) {
            throw new Exception("Bind failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return "Payment record successfully inserted with status 'Unpaid'.";
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

//--------------------------------------------------------------------------//

public function insertcAppointment($confirmationfill_id, $payableAmount) {
    $referenceNumber = $this->generateReferenceNumber();
    $sql = "INSERT INTO appointment_schedule (confirmation_id, payable_amount, status, p_status, reference_number)
            VALUES (?, ?,  'Process', 'Unpaid',?)";
    $stmt = $this->conn->prepare($sql);

    // Bind parameters: 'i' for integer (baptismfill_id, priest_id), 'd' for decimal/float (payable_amount)
    $stmt->bind_param("ids",$confirmationfill_id ,$payableAmount, $referenceNumber );

    // Execute the statement and check for errors
    if ($stmt->execute()) {
        return true; // Successful insertion
    } else {
        error_log("Insertion failed: " . $stmt->error);
        return false; // Insertion failed
    }

    $stmt->close();
}
public function getContactInfoAndTitles($confirmationfill_id) {
    $sql = "SELECT 
                c.fullname,
                c.email, 
                c.phone, 
                cf.event_name 
            FROM 
                citizen c 

            JOIN 
                confirmationfill cf ON c.citizend_id = cf.citizen_id 
            WHERE 
                cf.confirmationfill_id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $confirmationfill_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc(); // Returns an associative array with email, phone, and event_name
    } else {
        return false; // Returns false if no contact info is found
    }
}

public function approveConfirmation($confirmationfill_id) {
    try {
        $sql = "UPDATE confirmationfill SET status = 'Approved' WHERE confirmationfill_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $confirmationfill_id);
        
        if ($stmt->execute()) {
            return true;  // Status updated successfully
        } else {
            return false;  // Failed to update status
        }
    } catch (Exception $e) {
        return false;  // Error occurred
    }
}

//--------------------------------------------------------------------------------//
public function insertwAppointment($weddingffill_id, $payableAmount,$eventspeaker, $scheduleId) {
    $referenceNumber = $this->generateReferenceNumber();
   $sql = "INSERT INTO appointment_schedule (marriage_id, payable_amount,speaker_app,schedule_id, status, p_status,reference_number)
            VALUES (?, ?,?,?,  'Process', 'Unpaid',?)";
    $stmt = $this->conn->prepare($sql);

    // Bind parameters: 'i' for integer (baptismfill_id, priest_id), 'd' for decimal/float (payable_amount)
    $stmt->bind_param("idsis",$weddingffill_id ,$payableAmount,$eventspeaker,$scheduleId,$referenceNumber);

    // Execute the statement and check for errors
    if ($stmt->execute()) {
        return true; // Successful insertion
    } else {
        error_log("Insertion failed: " . $stmt->error);
        return false; // Insertion failed
    }

    $stmt->close();
}
public function getWeddingContactInfoAndTitles($weddingffill_id = null, $massweddingffill_id = null) {
    // Initialize the SQL query and the parameter
    $sql = "";
    $id = null;

    if ($massweddingffill_id) {
        // Use the mass baptism fill ID
        $sql = "
            SELECT 
            c.citizend_id, 
                c.fullname,
                c.email, 
                c.phone, 
                mf.event_name 
            FROM 
                citizen c 
            JOIN 
                marriagefill mf ON c.citizend_id = mf.citizen_id  
            WHERE 
            mf.marriagefill_id = ?";
        $id = $massweddingffill_id;
    } elseif ($weddingffill_id) {
        // Use the baptism fill ID
        $sql = "
       SELECT 
       c.citizend_id, 
                c.fullname,
                c.email, 
                c.phone, 
                mf.event_name 
            FROM 
                citizen c 
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id 
            JOIN 
                marriagefill mf ON s.schedule_id = mf.schedule_id 
            WHERE 
                mf.marriagefill_id = ?";
        $id = $weddingffill_id;
    }

    // If neither ID is provided, return false
    if (!$id) {
        return false;
    }

    // Prepare and execute the SQL query
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc(); // Returns an associative array with email, phone, and event_name
    } else {
        return false; // Returns false if no contact info is found
    }
}

public function approveWedding($weddingffill_id = null, $massweddingffill_id = null) {
    try {
        if ($weddingffill_id ) {
            $sql = "UPDATE marriagefill SET status = 'Approved' WHERE marriagefill_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $weddingffill_id);
        } elseif ($massweddingffill_id ) {
            $sql = "UPDATE marriagefill SET status = 'Approved' WHERE marriagefill_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $massweddingffill_id);
        } else {
            return false;  // Neither ID was provided
        }
        
        if ($stmt->execute()) {
            return true;  // Status updated successfully
        } else {
            return false;  // Failed to update status
        }
    } catch (Exception $e) {
        error_log("Error approving marriage or mass marriage: " . $e->getMessage());
        return false;  // Error occurred
    }
}

//--------------------------------------------------------------------------//
public function insertrAppointment($requestform_ids, $payableAmount) {
    $referenceNumber = $this->generateReferenceNumber();
   $sql = "INSERT INTO appointment_schedule (request_id, payable_amount,  status, p_status,reference_number)
            VALUES (?, ?, 'Process', 'Unpaid',?)";
    $stmt = $this->conn->prepare($sql);

    // Bind parameters: 'i' for integer (baptismfill_id, priest_id), 'd' for decimal/float (payable_amount)
    $stmt->bind_param("ids", $requestform_ids ,$payableAmount, $referenceNumber);

    // Execute the statement and check for errors
    if ($stmt->execute()) {
        return true; // Successful insertion
    } else {
        error_log("Insertion failed: " . $stmt->error);
        return false; // Insertion failed
    }

    $stmt->close();
}
public function insertfAppointment( $defuctomfill_id, $payableAmount) {
    $referenceNumber = $this->generateReferenceNumber();
   $sql = "INSERT INTO appointment_schedule (defuctom_id, payable_amount,  status, p_status,reference_number)
            VALUES (?, ?, 'Process', 'Unpaid',?)";
    $stmt = $this->conn->prepare($sql);

    // Bind parameters: 'i' for integer (baptismfill_id, priest_id), 'd' for decimal/float (payable_amount)
    $stmt->bind_param("ids", $defuctomfill_id ,$payableAmount, $referenceNumber);

    // Execute the statement and check for errors
    if ($stmt->execute()) {
        return true; // Successful insertion
    } else {
        error_log("Insertion failed: " . $stmt->error);
        return false; // Insertion failed
    }

    $stmt->close();
}
public function getRequestContactInfoAndTitles($defuctomfill_id) {
    $sql = "SELECT 
                c.fullname,
                c.email, 
                c.phone,
             rf.req_category
            FROM 
                citizen c 
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id 
            JOIN 
                req_form rf ON s.schedule_id = rf.schedule_id 
            WHERE 
                rf.req_id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $defuctomfill_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc(); // Returns an associative array with email, phone, and event_name
    } else {
        return false; // Returns false if no contact info is found
    }
}

public function getFuneralContactInfoAndTitles($defuctomfill_id) {
    $sql = "SELECT 
                c.fullname,
                c.email, 
                c.phone, 
                df.event_name 
            FROM 
                citizen c 
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id 
            JOIN 
                defuctomfill df ON s.schedule_id = df.schedule_id 
            WHERE 
                df.defuctomfill_id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $defuctomfill_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc(); // Returns an associative array with email, phone, and event_name
    } else {
        return false; // Returns false if no contact info is found
    }
}
public function approverequestform($requestform_ids) {
    try {
        $sql = "UPDATE req_form SET status = 'Approved' WHERE req_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $requestform_ids);
        
        if ($stmt->execute()) {
            return true;  // Status updated successfully
        } else {
            return false;  // Failed to update status
        }
    } catch (Exception $e) {
        return false;  // Error occurred
    }
}
public function approveFuneral( $defuctomfill_id) {
    try {
        $sql = "UPDATE defuctomfill SET status = 'Approved' WHERE defuctomfill_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",  $defuctomfill_id);
        
        if ($stmt->execute()) {
            return true;  // Status updated successfully
        } else {
            return false;  // Failed to update status
        }
    } catch (Exception $e) {
        return false;  // Error occurred
    }
}

//--------------------------------------------------------------------------//





public function fetchBaptismFill($status) {
    $query = "
    SELECT 
    pa.pr_reason AS reason,
    priest.fullname AS Priest,
    pa.pr_status AS priest_status,
    c.citizend_id AS citizen_id,
    b.fullname AS citizen_name,
    s.date AS schedule_date,
    s.start_time AS schedule_start_time,
    s.end_time AS schedule_end_time,
    b.event_name AS event_name,
    b.status AS approval_status,
    b.role AS roles,
    b.baptism_id AS id,
    'Baptism' AS type,
    b.father_fullname,
    b.pbirth,
    b.mother_fullname,
    b.religion,
    b.parent_resident,
    b.godparent,
    b.gender,
    b.c_date_birth,
    b.age,
    b.address,
    b.created_at
FROM 
    schedule s
JOIN 
    baptismfill b ON s.schedule_id = b.schedule_id
LEFT JOIN 
    citizen c ON c.citizend_id = b.citizen_id
LEFT JOIN 
    priest_approval pa ON pa.approval_id = b.approval_id
LEFT JOIN 
    citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
WHERE 
    b.status = ? ";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

public function fetchConfirmationFill($status) {
    $query = "
        SELECT 
        pa.pr_reason AS reason,
        priest.fullname AS Priest,
       pa.pr_status AS priest_status,
            c.citizend_id,
            cf.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_start_time,
            s.end_time AS schedule_end_time,
            cf.event_name AS event_name,
            cf.status AS approval_status,
            cf.role AS roles,
            cf.confirmationfill_id AS id,
            'Confirmation' AS type,
        
            cf.father_fullname,
            cf.date_of_baptism,
            cf.mother_fullname,
            cf.permission_to_confirm,
            cf.church_address,
            cf.name_of_church,
            cf.c_gender,
            cf.c_date_birth,
            cf.c_address,
            cf.c_created_at 
            FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
 
        JOIN 
            confirmationfill cf ON s.schedule_id = cf.schedule_id
            LEFT JOIN 
            priest_approval pa ON pa.approval_id = cf.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
        WHERE 
            cf.status = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

public function fetchMarriageFill($status) {
    $query = "
        SELECT 
        pa.pr_reason AS reason,
        priest.fullname AS Priest,
        pa.pr_status AS priest_status,
            c.citizend_id,
            c.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_start_time,
            s.end_time AS schedule_end_time,
            mf.event_name AS event_name,
            mf.status AS approval_status,
            mf.role AS roles,
            mf.marriagefill_id AS id,
            'Marriage' AS type,
            mf.groom_name,
            mf.groom_dob,
            mf.groom_age,
            mf.groom_place_of_birth,
            mf.groom_citizenship,
            mf.groom_address,
            mf.groom_religion,
            mf.groom_previously_married,
            mf.bride_name,
            mf.bride_dob,
            mf.bride_age,
            mf.bride_place_of_birth,
            mf.bride_citizenship,
            mf.bride_address,
            mf.bride_religion,
            mf.bride_previously_married,
            mf.m_created_at 
            FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
   
        JOIN 
            marriagefill mf ON s.schedule_id = mf.schedule_id
            LEFT JOIN 
            priest_approval pa ON pa.approval_id = mf.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
        WHERE 
            mf.status = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

public function fetchDefuctomFill($status) {
    $query = "
        SELECT 
        pa.pr_reason AS reason,
        priest.fullname AS Priest,
        pa.pr_status AS priest_status,
            c.citizend_id,
            c.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_start_time,
            s.end_time AS schedule_end_time,
            df.event_name AS event_name,
            df.status AS approval_status,
            df.role AS roles,
            df.defuctomfill_id AS id,
            'Defuctom' AS type,
            df.d_fullname,
            df.d_address,
            df.father_fullname,
            df.place_of_birth,
            df.mother_fullname,
            df.cause_of_death,
            df.marital_status,
            df.place_of_death,
            df.d_gender,
            df.date_of_birth,
            df.date_of_death,
            df.parents_residence,
            df.d_created_at 
            FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 

        JOIN 
            defuctomfill df ON s.schedule_id = df.schedule_id
            LEFT JOIN 
            priest_approval pa ON pa.approval_id = df.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
        WHERE 
            df.status = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

public function getPendingCitizen($eventType = null, $status = 'Pending') {
    $results = [];

    switch ($eventType) {
        case 'Baptism':
            $results = $this->fetchBaptismFill($status);
            break;
        case 'Confirmation':
            $results = $this->fetchConfirmationFill($status);
            break;
        case 'Marriage':
            $results = $this->fetchMarriageFill($status);
            break;
        case 'Defuctom':
            $results = $this->fetchDefuctomFill($status);
            break;
        default:
            $results = array_merge(
                $this->fetchBaptismFill($status),
                $this->fetchConfirmationFill($status),
                $this->fetchMarriageFill($status),
                $this->fetchDefuctomFill($status)
            );
            break;
    }

    // Sort the results based on created_at timestamp first
    usort($results, function($a, $b) {
        // Determine the correct created_at field for each event type
        $createdAtFieldA = $a['type'] === 'Baptism' ? $a['created_at'] :
                           ($a['type'] === 'Confirmation' ? $a['c_created_at'] :
                           ($a['type'] === 'Marriage' ? $a['m_created_at'] :
                           $a['d_created_at'])); // Adjust based on your actual fields

        $createdAtFieldB = $b['type'] === 'Baptism' ? $b['created_at'] :
                           ($b['type'] === 'Confirmation' ? $b['c_created_at'] :
                           ($b['type'] === 'Marriage' ? $b['m_created_at'] :
                           $b['d_created_at'])); // Adjust based on your actual fields

        // Convert created_at timestamps to UNIX timestamps for comparison
        $aCreatedAt = strtotime($createdAtFieldA ?? '0');
        $bCreatedAt = strtotime($createdAtFieldB ?? '0');

        // First, sort by created_at timestamp (ascending order)
        if ($aCreatedAt !== $bCreatedAt) {
            return $aCreatedAt - $bCreatedAt; // Ascending order
        }

        // If created_at timestamps are the same, then sort by event type
        $eventOrder = ['Baptism', 'Confirmation', 'Marriage', 'Defuctom'];
        return array_search($a['type'], $eventOrder) - array_search($b['type'], $eventOrder);
    });

    return $results;
}

public function getConfirmationAppointments($statusFilter = null) {
    $sql = "SELECT 
        a.reference_number AS ref_number,
        'Confirmation' AS type,
        cf.status AS status,
        cf.fullname AS fullnames,
        pa.pr_status AS approve_priest,
        cf.confirmationfill_id AS id,
        cf.role AS roles,
        cf.c_date_birth AS birth,
        cf.c_age AS age,
        cf.event_name AS Event_Name,
        c.fullname AS citizen_name, 
        s.date AS schedule_date,
        s.start_time AS schedule_time,
        a.appsched_id,
        a.baptismfill_id,
        pa.priest_id,
        priest.fullname AS priest_name,
        a.schedule_id AS appointment_schedule_id,
        a.payable_amount AS payable_amount,
        a.status AS c_status,
        a.p_status AS p_status,
       
        cf.c_created_at 
    FROM 
        schedule s
    LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
    JOIN confirmationfill cf ON s.schedule_id = cf.schedule_id
    JOIN appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
 
    JOIN priest_approval pa ON pa.approval_id = cf.approval_id
    LEFT JOIN citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
    WHERE 
        cf.status != 'Pending' AND 
        cf.status = 'Approved' ";
    
    // Modify query based on statusFilter
    if ($statusFilter === 'CompletedPaid') {
        $sql .= " AND (a.status = 'Completed' AND a.p_status = 'Paid')";
    } else {
        $sql .= " AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
    }

    return $this->fetchAppointments($sql);
}


public function getBaptismAppointments($statusFilter = null) {
    $sql = "SELECT 
      a.reference_number AS ref_number,
        'Baptism' AS type,
        b.status AS status,
        b.fullname AS fullnames,
        pa.pr_status AS approve_priest,
        b.baptism_id AS id,
        b.role AS roles,
        b.event_name AS Event_Name,
        c.fullname AS citizen_name, 
        s.date AS schedule_date,
        s.start_time AS schedule_time,
        a.appsched_id,
        a.baptismfill_id,
        pa.priest_id,
        priest.fullname AS priest_name,
        a.schedule_id AS appointment_schedule_id,
        a.payable_amount AS payable_amount,
        a.status AS c_status,
        a.p_status AS p_status,
        sch.date AS appointment_schedule_date,  
        sch.start_time AS appointment_schedule_start_time,
        sch.end_time AS appointment_schedule_end_time,
        b.created_at 
    FROM 
        schedule s
    LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
    JOIN baptismfill b ON s.schedule_id = b.schedule_id
    JOIN appointment_schedule a ON b.baptism_id = a.baptismfill_id
    JOIN schedule sch ON a.schedule_id = sch.schedule_id  
    JOIN priest_approval pa ON pa.approval_id = b.approval_id
    LEFT JOIN citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
    WHERE 
        b.status != 'Pending' AND 
        b.status = 'Approved'";
    
    // Modify query based on statusFilter
    if ($statusFilter === 'CompletedPaid') {
        $sql .= " AND (a.status = 'Completed' AND a.p_status = 'Paid')";
    } else {
        $sql .= " AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
    }

    return $this->fetchAppointments($sql);
}

public function getMarriageAppointments($statusFilter = null) {
    $sql = "SELECT 
      a.reference_number AS ref_number,
        'Marriage' AS type,
        mf.status AS status,
        mf.groom_name AS fullnames,
        pa.pr_status AS approve_priest,
        mf.marriagefill_id AS id,
        mf.role AS roles,
        mf.event_name AS Event_Name,
        c.fullname AS citizen_name,
        s.date AS schedule_date,
        s.start_time AS schedule_time,
        a.appsched_id,
        a.marriage_id,
        pa.priest_id,
        priest.fullname AS priest_name,
        a.schedule_id AS appointment_schedule_id,
        a.payable_amount AS payable_amount,
        a.status AS c_status,
        a.p_status AS p_status,
        sch.date AS appointment_schedule_date,  
        sch.start_time AS appointment_schedule_start_time,
        sch.end_time AS appointment_schedule_end_time,
        mf.m_created_at 
    FROM 
        schedule s
    LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
    JOIN marriagefill mf ON s.schedule_id = mf.schedule_id
    JOIN appointment_schedule a ON mf.marriagefill_id = a.marriage_id
    JOIN schedule sch ON a.schedule_id = sch.schedule_id
    JOIN priest_approval pa ON pa.approval_id = mf.approval_id
    LEFT JOIN citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
    WHERE 
        mf.status != 'Pending' AND 
        mf.status = 'Approved' ";
    
    // Modify query based on statusFilter
    if ($statusFilter === 'CompletedPaid') {
        $sql .= " AND (a.status = 'Completed' AND a.p_status = 'Paid')";
    } else {
        $sql .= " AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
    }

    return $this->fetchAppointments($sql);
}

public function getDefuctomAppointments($statusFilter = null) {
    $sql = "SELECT 
      a.reference_number AS ref_number,
        'Defuctom' AS type,
        df.status AS status,
        df.d_fullname AS fullnames,
        pa.pr_status AS approve_priest,
        df.defuctomfill_id AS id,
        df.role AS roles,
        df.event_name AS Event_Name,
        c.fullname AS citizen_name,
        s.date AS schedule_date,
        s.start_time AS schedule_time,
        a.appsched_id,
        a.defuctom_id,  
        pa.priest_id,
        priest.fullname AS priest_name,
        a.schedule_id AS appointment_schedule_id,
        a.payable_amount AS payable_amount,
        a.status AS c_status,
        a.p_status AS p_status,
        df.d_created_at 
    FROM 
        schedule s
    LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
    JOIN defuctomfill df ON s.schedule_id = df.schedule_id
    JOIN appointment_schedule a ON df.defuctomfill_id = a.defuctom_id
    JOIN priest_approval pa ON pa.approval_id = df.approval_id
    LEFT JOIN citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
    WHERE 
        df.status != 'Pending' AND 
        df.status = 'Approved' ";

    // Modify query based on statusFilter
    if ($statusFilter === 'CompletedPaid') {
        $sql .= " AND (a.status = 'Completed' AND a.p_status = 'Paid')";
    } else {
        $sql .= " AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
    }

    return $this->fetchAppointments($sql);
}

private function fetchAppointments($sql) {
        $result = $this->conn->query($sql);
        
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        $appointments = [];
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        return $appointments;
    }
    
    public function getPendingAppointments($statusFilter = null) {
        $confirmationAppointments = $this->getConfirmationAppointments($statusFilter);
        $baptismAppointments = $this->getBaptismAppointments($statusFilter);
        $marriageAppointments = $this->getMarriageAppointments($statusFilter);
        $defuctomAppointments = $this->getDefuctomAppointments($statusFilter);
    
        // Combine all appointments into one array
        $allAppointments = array_merge($baptismAppointments, $confirmationAppointments, $marriageAppointments, $defuctomAppointments);
    
        // Sort all appointments based on the correct created_at timestamp for each event type
        usort($allAppointments, function($a, $b) {
            // Determine the correct created_at field for each event type
            $createdAtFieldA = $a['type'] === 'Confirmation' ? $a['c_created_at'] :
                                ($a['type'] === 'Baptism' ? $a['created_at'] :
                                ($a['type'] === 'Marriage' ? $a['m_created_at'] :
                                ($a['type'] === 'Defuctom' ? $a['d_created_at'] : '0'))); // Adjust based on your actual fields
    
            $createdAtFieldB = $b['type'] === 'Confirmation' ? $b['c_created_at'] :
                                ($b['type'] === 'Baptism' ? $b['created_at'] :
                                ($b['type'] === 'Marriage' ? $b['m_created_at'] :
                                ($b['type'] === 'Defuctom' ? $b['d_created_at'] : '0'))); // Adjust based on your actual fields
    
            // Convert created_at timestamps to UNIX timestamps for comparison
            $aCreatedAt = strtotime($createdAtFieldA ?? '0');
            $bCreatedAt = strtotime($createdAtFieldB ?? '0');
    
            // First, sort by created_at timestamp (ascending order)
            if ($aCreatedAt !== $bCreatedAt) {
                return $aCreatedAt - $bCreatedAt; // Ascending order
            }
    
            // If created_at timestamps are the same, then sort by event type
            $eventOrder = ['Baptism', 'Confirmation', 'Marriage', 'Defuctom'];
            return array_search($a['type'], $eventOrder) - array_search($b['type'], $eventOrder);
        });
    
        return $allAppointments;
    }
    

    
    public function getPendingMassAppointments($statusFilter = null) {
        // Base SQL query
        $sql = "SELECT 
        a.reference_number AS ref_number,
                b.baptism_id AS id,
                b.role AS roles,
                b.event_name AS Event_Name,
                b.fullname AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_time,
                a.payable_amount AS payable_amount,
                a.status AS c_status,
                a.p_status AS p_status,
                a.appsched_id,
                b.created_at AS created_at
            FROM 
                baptismfill b
            JOIN 
                announcement an ON b.announcement_id = an.announcement_id
            JOIN 
                appointment_schedule a ON b.baptism_id = a.baptismfill_id
            JOIN 
                schedule s ON an.schedule_id = s.schedule_id
            WHERE 
                1 = 1";
        
        // Apply filters based on the selected status filter
        if ($statusFilter === 'CompletedPaid') {
            $sql .= " AND (a.status = 'Completed' AND a.p_status = 'Paid')";
        } else {
            // Default filter for 'Process' or 'Unpaid'
            $sql .= " AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
        }
    
        // Add the rest of the UNIONs for confirmation and marriage
        $sql .= " UNION ALL
            SELECT 
            a.reference_number AS ref_number,
                cf.confirmationfill_id AS id,
                cf.role AS roles,
                cf.event_name AS Event_Name,
                cf.fullname AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_time,
                a.payable_amount AS payable_amount,
                a.status AS c_status,
                a.p_status AS p_status,
                a.appsched_id,
                cf.c_created_at AS created_at
            FROM 
                confirmationfill cf
            JOIN 
                announcement an ON cf.announcement_id = an.announcement_id
            JOIN 
                appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
            JOIN 
                schedule s ON an.schedule_id = s.schedule_id
            WHERE 
                1 = 1";
    
        if ($statusFilter === 'CompletedPaid') {
            $sql .= " AND (a.status = 'Completed' AND a.p_status = 'Paid')";
        } else {
            $sql .= " AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
        }
    
        $sql .= " UNION ALL
            SELECT 
            a.reference_number AS ref_number,
                mf.marriagefill_id AS id,
                mf.role AS roles,
                mf.event_name AS Event_Name,
                CONCAT(mf.groom_name, ' and ', mf.bride_name) AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_time,
                a.payable_amount AS payable_amount,
                a.status AS c_status,
                a.p_status AS p_status,
                a.appsched_id,
                mf.m_created_at AS created_at
            FROM 
                marriagefill mf
            JOIN 
                announcement an ON mf.announcement_id = an.announcement_id
            JOIN 
                appointment_schedule a ON mf.marriagefill_id = a.marriage_id
            JOIN 
                schedule s ON an.schedule_id = s.schedule_id
            WHERE 
                1 = 1";
    
        if ($statusFilter === 'CompletedPaid') {
            $sql .= " AND (a.status = 'Completed' AND a.p_status = 'Paid')";
        } else {
            $sql .= " AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
        }
    
        // Final ordering
        $sql .= " ORDER BY created_at ASC";
    
        // Execute the query
        $result = $this->conn->query($sql);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        $pendingMassCitizen = [];
        while ($row = $result->fetch_assoc()) {
            $pendingMassCitizen[] = $row;
        }
    
        return $pendingMassCitizen;
    }
    
    
    public function getBaptismPendingCitizen($status) {
        $query = "SELECT 
                a.title AS Event_Name,
                b.fullname AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_start_time,
                s.end_time AS schedule_end_time,
                b.event_name AS event_name,
                b.status AS approval_status,
                b.role AS roles,
                b.baptism_id AS id,
                'MassBaptism' AS type, -- Use 'MassBaptism' for consistent event type naming
                b.father_fullname,
                b.pbirth,
                b.mother_fullname,
                b.religion,
                b.parent_resident,
                b.godparent,
                b.gender,
                b.c_date_birth,
                b.age,
                b.address,
                b.created_at 
            FROM 
                announcement a
            JOIN 
                baptismfill b ON a.announcement_id = b.announcement_id
            JOIN 
                schedule s ON a.schedule_id = s.schedule_id
            WHERE 
                b.status = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getConfirmationPendingCitizen($status) {
        $query = "SELECT 
                a.title AS Event_Name,
               
                s.date AS schedule_date,
                s.start_time AS schedule_start_time,
                s.end_time AS schedule_end_time,
                cf.event_name AS event_name,
                cf.status AS approval_status,
                cf.role AS roles,
                cf.confirmationfill_id AS id,
                'Mass Confirmation' AS type, -- Use 'Mass Confirmation'
                cf.fullname AS citizen_name,
                cf.father_fullname,
                cf.date_of_baptism,
                cf.mother_fullname,
                cf.permission_to_confirm,
                cf.church_address,
                cf.name_of_church,
                cf.c_gender,
                cf.c_date_birth,
                cf.c_address,
                cf.c_created_at -- Use the actual created_at field here
            FROM 
                confirmationfill cf
          
            JOIN 
                announcement a ON cf.announcement_id = a.announcement_id
            JOIN 
                schedule s ON a.schedule_id = s.schedule_id
            WHERE 
                cf.status = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getMarriagePendingCitizen($status) {
        $query = "SELECT 
                a.title AS Event_Name,
          
                s.date AS schedule_date,
                s.start_time AS schedule_start_time,
                s.end_time AS schedule_end_time,
                mf.event_name AS event_name,
                mf.status AS approval_status,
                mf.role AS roles,
                mf.marriagefill_id AS id,
                'Mass Marriage' AS type, -- Use 'Mass Marriage'
                CONCAT(mf.bride_name, ' & ', mf.groom_name) AS citizen_name,
                mf.groom_name ,
                mf.groom_dob,
                mf.groom_age,
                mf.groom_place_of_birth,
                mf.groom_citizenship,
                mf.groom_address,
                mf.groom_religion,
                mf.groom_previously_married,
                mf.bride_name,
                mf.bride_dob,
                mf.bride_age,
                mf.bride_place_of_birth,
                mf.bride_citizenship,
                mf.bride_address,
                mf.bride_religion,
                mf.bride_previously_married,
                mf.m_created_at -- Use the actual created_at field here
            FROM 
                marriagefill mf
           
            JOIN 
                announcement a ON mf.announcement_id = a.announcement_id
            JOIN 
                schedule s ON a.schedule_id = s.schedule_id
            WHERE 
                mf.status = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getMassPendingCitizen($eventType = null, $status = 'Pending') {
        // Fetch pending citizens based on event type
        switch ($eventType) {
            case 'MassBaptism':
                $pendingCitizens = $this->getBaptismPendingCitizen($status);
                break;
            case 'Mass Confirmation':
                $pendingCitizens = $this->getConfirmationPendingCitizen($status);
                break;
            case 'Mass Marriage':
                $pendingCitizens = $this->getMarriagePendingCitizen($status);
                break;
            default:
                // Combine all event types if no specific eventType is provided
                $pendingCitizens = array_merge(
                    $this->getBaptismPendingCitizen($status),
                    $this->getConfirmationPendingCitizen($status),
                    $this->getMarriagePendingCitizen($status)
                );
                break;
        }
    
        // Sort the results based on created_at timestamp first
        usort($pendingCitizens, function($a, $b) {
            // Determine the correct created_at field for each event type
            $createdAtFieldA = $a['type'] === 'MassBaptism' ? $a['created_at'] :
                               ($a['type'] === 'Mass Confirmation' ? $a['c_created_at'] :  // Use the same created_at field
                               ($a['type'] === 'Mass Marriage' ? $a['m_created_at'] :
                               '0')); // Adjust if necessary
    
            $createdAtFieldB = $b['type'] === 'MassBaptism' ? $b['created_at'] :
                               ($b['type'] === 'Mass Confirmation' ? $b['c_created_at'] :  // Use the same created_at field
                               ($b['type'] === 'Mass Marriage' ? $b['m_created_at'] :
                               '0')); // Adjust if necessary
    
            // Convert created_at timestamps to UNIX timestamps for comparison
            $aCreatedAt = strtotime($createdAtFieldA ?? '0');
            $bCreatedAt = strtotime($createdAtFieldB ?? '0');
    
            // First, sort by created_at timestamp (ascending order)
            if ($aCreatedAt !== $bCreatedAt) {
                return $aCreatedAt - $bCreatedAt; // Ascending order
            }
    
            // If created_at timestamps are the same, then sort by event type
            $eventOrder = ['MassBaptism', 'Mass Confirmation', 'Mass Marriage'];
            return array_search($a['type'], $eventOrder) - array_search($b['type'], $eventOrder);
        });
    
        return $pendingCitizens;
    }
    
    
    
        
    public function getCurrentUsers() {
        $sql = "SELECT `citizend_id`, `fullname`, `email`, `gender`, `phone`, `c_date_birth`, `age`, `address`, `valid_id`, `password`, `user_type`, `r_status`, `c_current_time` 
                FROM `citizen` 
                WHERE `c_current_time` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND `r_status` = 'Pending'";
        $result = $this->conn->query($sql);

        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }

        $CurrentUsers = [];
        while ($row = $result->fetch_assoc()) {
            $CurrentUsers[] = $row;
        }
        return $CurrentUsers;
    }
    public function getUnreadNotificationCount() {
        $query = "SELECT COUNT(*) AS count FROM notifications WHERE status = 'unread'";
        $result = $this->conn->query($query);

        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }

        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getRecentCitizenUpdates() {
        $query = "SELECT citizend_id, fullname, c_current_time 
                  FROM citizen 
                  WHERE YEARWEEK(c_current_time, 1) = YEARWEEK(CURDATE(), 1)
                  AND r_status = 'Pending'
                  ORDER BY c_current_time DESC";
        $result = $this->conn->query($query);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getRecentBaptismFills() {
        $query = "SELECT baptism_id, fullname, event_name, created_at AS c_current_time  
                  FROM baptismfill 
                  WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
                  AND status = 'Pending'
                  AND role = 'Online'
                  ORDER BY created_at DESC";
        $result = $this->conn->query($query);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getRecentPriestBaptismFills() {
        $query = "SELECT b.baptism_id AS priestbaptism, priest.fullname AS fullname, b.event_name,pa.pr_status AS checkstatus, pa.assigned_time AS c_current_time  
        FROM baptismfill b
        LEFT JOIN 
priest_approval pa ON pa.approval_id = b.approval_id
LEFT JOIN 
citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
        WHERE YEARWEEK(pa.assigned_time, 1) = YEARWEEK(CURDATE(), 1)
       AND pa.pr_status IN ('Approved', 'Declined') 
        AND b.status = 'Pending'
        AND b.role = 'Online'
        ORDER BY pa.assigned_time DESC";
        $result = $this->conn->query($query);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getRecentPriestConfirmationFills() {
        $query = "SELECT cf.confirmationfill_id AS priestconfirmation,priest.fullname AS fullname, cf.event_name,pa.pr_status AS checkstatus, pa.assigned_time AS c_current_time  
        FROM confirmationfill cf
        LEFT JOIN 
priest_approval pa ON pa.approval_id = cf.approval_id
LEFT JOIN 
citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
        WHERE YEARWEEK(pa.assigned_time, 1) = YEARWEEK(CURDATE(), 1)
        AND pa.pr_status IN ('Approved', 'Declined') 
        AND status = 'Pending'
        AND role = 'Online'
        ORDER BY pa.assigned_time DESC";
        $result = $this->conn->query($query);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getRecentPriestDefuctomFills() {
        $query = "SELECT df.defuctomfill_id AS priestdefuctom,priest.fullname AS fullname, df.event_name,pa.pr_status AS checkstatus, pa.assigned_time AS c_current_time  
                   
                  FROM defuctomfill df 
                  LEFT JOIN 
priest_approval pa ON pa.approval_id = df.approval_id
LEFT JOIN 
citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
                  WHERE YEARWEEK(pa.assigned_time, 1) = YEARWEEK(CURDATE(), 1)
                  AND pa.pr_status IN ('Approved', 'Declined') 
                  AND status = 'Pending'
                  AND role = 'Online'
                  ORDER BY pa.assigned_time DESC";
        $result = $this->conn->query($query);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getRecentPriestMarriageFills() {
        $query = "SELECT mf.marriagefill_id AS priestmarriage, CONCAT(mf.groom_name, ' and ', mf.bride_name) AS full_names,priest.fullname AS fullname, mf.event_name,pa.pr_status AS checkstatus,pa.assigned_time AS c_current_time  
                  FROM marriagefill mf
                  LEFT JOIN 
priest_approval pa ON pa.approval_id = mf.approval_id
LEFT JOIN 
citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
                  WHERE YEARWEEK(pa.assigned_time, 1) = YEARWEEK(CURDATE(), 1)
                  AND pa.pr_status IN ('Approved', 'Declined') 
                  AND status = 'Pending'
                  AND role = 'Online'
                  ORDER BY pa.assigned_time DESC";
        $result = $this->conn->query($query);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getRecentPriestRequestFormFills() {
        $query = "SELECT rf.req_id AS requestpriest,  rf.req_person, rf.req_category,pa.assigned_time AS c_current_time,pa.pr_status AS checkstatus,priest.fullname AS fullname
        FROM req_form rf
             LEFT JOIN 
priest_approval pa ON pa.approval_id = rf.approval_id
LEFT JOIN 
citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
             WHERE YEARWEEK(pa.assigned_time, 1) = YEARWEEK(CURDATE(), 1)
             AND pa.pr_status IN ('Approved', 'Declined') 
             AND status = 'Pending'
             AND role = 'Online'
             ORDER BY pa.assigned_time DESC";
        $result = $this->conn->query($query);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    
    public function getRecentConfirmationFills() {
        $query = "SELECT confirmationfill_id, fullname, event_name, c_created_at AS c_current_time  
                  FROM confirmationfill 
                  WHERE YEARWEEK(c_created_at, 1) = YEARWEEK(CURDATE(), 1)
                  AND status = 'Pending'
                  AND role = 'Online'
                  ORDER BY c_created_at DESC";
        $result = $this->conn->query($query);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getRecentDefuctomFills() {
        $query = "SELECT defuctomfill_id, d_fullname AS fullname, event_name, d_created_at AS c_current_time  
                  FROM defuctomfill 
                  WHERE YEARWEEK(d_created_at, 1) = YEARWEEK(CURDATE(), 1)
                  AND status = 'Pending'
                  AND role = 'Online'
                  ORDER BY d_created_at DESC";
        $result = $this->conn->query($query);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getRecentMarriageFills() {
        $query = "SELECT mf.marriagefill_id, CONCAT(mf.groom_name, ' and ', mf.bride_name) AS full_names, mf.event_name, mf.m_created_at AS c_current_time  
                  FROM marriagefill mf
                  WHERE YEARWEEK(mf.m_created_at, 1) = YEARWEEK(CURDATE(), 1)
                  AND status = 'Pending'
                  AND role = 'Online'
                  ORDER BY mf.m_created_at DESC";
        $result = $this->conn->query($query);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getRecentRequestFormFills() {
        $query = "SELECT rf.req_id,  req_person, rf.req_category, rf.created_at AS c_current_time  
                  FROM req_form rf
                  WHERE YEARWEEK(rf.created_at, 1) = YEARWEEK(CURDATE(), 1)
                  AND status = 'Pending'
                  AND role = 'Online'
                  ORDER BY rf.created_at DESC";
        $result = $this->conn->query($query);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }



    public function getApprovedRegistrations() {
        $sql = "SELECT `citizend_id`, `fullname`, `email`, `gender`, `phone`, `c_date_birth`, `age`, `address`, `valid_id`, `password`, `user_type`, `r_status`, `c_current_time`
                FROM `citizen`
                WHERE `r_status` = 'Approved'";
        $result = $this->conn->query($sql);

        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }

        $approvedRegistrations = [];
        while ($row = $result->fetch_assoc()) {
            $approvedRegistrations[] = $row;
        }
        return $approvedRegistrations;
    }

    public function addAnnouncement($announcementData, $scheduleData, $scheduleDatas, $approvalId) {
        // SQL statements
        $scheduleSql = "INSERT INTO schedule(date, start_time, end_time) VALUES (?, ?, ?)";
        $priestApprovalSql = "INSERT INTO priest_approval(priest_id, pr_status, assigned_time) VALUES (?, 'Approved', NOW())";
        $announcementSql = "INSERT INTO announcement(approval_id, event_type, title, description, schedule_id, seminar_id, date_created, capacity,speaker_ann) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";
    
        $this->conn->begin_transaction();
    
        try {
            // Insert schedule for announcement
            $scheduleStmt = $this->conn->prepare($scheduleSql);
            $scheduleStmt->bind_param("sss", $scheduleData['date'], $scheduleData['start_time'], $scheduleData['end_time']);
            $scheduleStmt->execute();
    
            // Get the last inserted schedule_id for announcement
            $scheduleId = $this->conn->insert_id;
    
            // Insert schedule for seminar
            $scheduleStmt2 = $this->conn->prepare($scheduleSql);
            $scheduleStmt2->bind_param("sss", $scheduleDatas['date'], $scheduleDatas['start_time'], $scheduleDatas['end_time']);
            $scheduleStmt2->execute();
    
            // Get the last inserted schedule_id for seminar
            $seminarId = $this->conn->insert_id;
    
            // Insert into priest_approval
            $priestApprovalStmt = $this->conn->prepare($priestApprovalSql);
            $priestApprovalStmt->bind_param("i", $approvalId);
            $priestApprovalStmt->execute();
    
            // Get the last inserted approval_id
            $approvalId = $this->conn->insert_id;
    
            // Insert announcement using approval_id
            $announcementStmt = $this->conn->prepare($announcementSql);
            $announcementStmt->bind_param("isssiisis", 
                $approvalId,  // approval_id from priest_approval
                $announcementData['event_type'], 
                $announcementData['title'], 
                $announcementData['description'], 
                $scheduleId,  // schedule_id for announcement
                $seminarId,   // schedule_id for seminar
                $announcementData['date_created'], 
                $announcementData['capacity'],
                $announcementData['speaker_ann']
            );
            $announcementStmt->execute();
    
            // Commit the transaction
            $this->conn->commit();
    
            // Close the statements
            $scheduleStmt->close();
            $scheduleStmt2->close();
            $priestApprovalStmt->close();
            $announcementStmt->close();
    
            return true;
    
        } catch (Exception $e) {
            // Rollback transaction if something went wrong
            $this->conn->rollback();
    
            // Close the statements if they are open
            if ($scheduleStmt) $scheduleStmt->close();
            if ($scheduleStmt2) $scheduleStmt2->close();
            if ($priestApprovalStmt) $priestApprovalStmt->close();
            if ($announcementStmt) $announcementStmt->close();
    
            return false;
        }
    }
    
    public function fetchRequestFormEvents() {
        $query = "
            SELECT 
              
                rf.req_person AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_starttime,
                s.end_time AS schedule_endtime,
                rf.req_person AS Event_Name,
                rf.status AS approval_status,
                rf.req_id AS event_id
            FROM 
                schedule s
            JOIN 
                req_form rf ON s.schedule_id = rf.schedule_id
            JOIN 
                appointment_schedule a ON rf.req_id = a.request_id
            WHERE 
                rf.status = 'Approved';
        ";
        return $this->executeQuery($query);
    }
    public function fetchMarriageEvents() {
        $query = "
            SELECT 
                mf.groom_name AS groom_name,
                mf.bride_name AS bride_name,
                s.date AS schedule_date,
                s.start_time AS schedule_starttime,
                s.end_time AS schedule_endtime,
                mf.event_name AS Event_Name,
                mf.status AS approval_status,
                mf.marriagefill_id AS event_id
            FROM 
                schedule s
            JOIN 
                marriagefill mf ON s.schedule_id = mf.schedule_id
            JOIN 
                appointment_schedule a ON mf.marriagefill_id = a.marriage_id
            WHERE 
                mf.status = 'Approved';
        ";
        return $this->executeQuery($query);
    }

    // Fetch baptism events
    public function fetchBaptismEvents() {
        $query = "
            SELECT 
                b.fullname AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_starttime,
                s.end_time AS schedule_endtime,
                b.event_name AS Event_Name,
                b.status AS approval_status,
                b.baptism_id AS event_id
            FROM 
                schedule s
            JOIN 
                baptismfill b ON s.schedule_id = b.schedule_id
            JOIN 
                appointment_schedule a ON b.baptism_id = a.baptismfill_id
            WHERE 
                b.status = 'Approved';
        ";
        return $this->executeQuery($query);
    }

    // Fetch confirmation events
    public function fetchConfirmationEvents() {
        $query = "
            SELECT 
                cf.fullname AS fullname,
                s.date AS schedule_date,
                s.start_time AS schedule_starttime,
                s.end_time AS schedule_endtime,
                cf.event_name AS Event_Name,
                cf.status AS approval_status,
                cf.confirmationfill_id AS event_id
            FROM 
                schedule s
            JOIN 
                confirmationfill cf ON s.schedule_id = cf.schedule_id
            JOIN 
                appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
            WHERE 
                cf.status = 'Approved';
        ";
        return $this->executeQuery($query);
    }

    // Fetch defuctom events
    public function fetchDefuctomEvents() {
        $query = "
            SELECT 
                df.d_fullname AS fullname,
                s.date AS schedule_date,
                s.start_time AS schedule_starttime,
                s.end_time AS schedule_endtime,
                df.event_name AS Event_Name,
                df.status AS approval_status,
                df.defuctomfill_id AS event_id
            FROM 
                schedule s
            JOIN 
                defuctomfill df ON s.schedule_id = df.schedule_id
            JOIN 
                appointment_schedule a ON df.defuctomfill_id = a.defuctom_id
            WHERE 
                df.status = 'Approved';
        ";
        return $this->executeQuery($query);
    }
    public function fetchAddCalendar() {
        $query = "
            SELECT cal_fullname,cal_Category,cal_description,cal_date FROM 
                event_calendar";
        return $this->executeQuery($query);
    }

    // Fetch mass events
    public function fetchMassEvents() {
        $query = "
            SELECT 
                announcement.announcement_id,
                announcement.event_type,
                announcement.title,
                announcement.description,
                announcement.date_created,
                announcement.capacity,
                schedule.date,
                schedule.start_time,
                schedule.end_time
            FROM 
                announcement
            JOIN 
                schedule ON announcement.schedule_id = schedule.schedule_id
            ORDER BY 
                date_created DESC;
        ";
        return $this->executeQuery($query);
    }

    // Execute the query and return results
    private function executeQuery($query) {
        $result = mysqli_query($this->conn, $query);
        if (!$result) {
            die("Database query failed: " . mysqli_error($this->conn));
        }
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    
}
?>
