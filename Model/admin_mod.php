<?php
class Admin {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    
    }   public function addDonation($d_name, $amount, $donated_on, $description) {
        $stmt = $this->conn->prepare("INSERT INTO donation (d_name, amount, donated_on, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $d_name, $amount, $donated_on, $description);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function generateDonationReport($dateRange) {
        // Initialize the date condition and end date
        $dateCondition = '';
        $startDate = null;
        $endDate = date('Y-m-d H:i:s'); // Current date and time
    
        // Determine the start date based on the date range
        if ($dateRange) {
            switch ($dateRange) {
                case '7':
                    $startDate = date('Y-m-d H:i:s', strtotime('-7 days'));
                    break;
                case '15':
                    $startDate = date('Y-m-d H:i:s', strtotime('-15 days'));
                    break;
                case '30':
                    $startDate = date('Y-m-d H:i:s', strtotime('-30 days'));
                    break;
                case '365':
                    $startDate = date('Y-m-d H:i:s', strtotime('-1 year'));
                    break;
            }
            
            // Set the date condition for the SQL query if a start date is determined
            if ($startDate) {
                $dateCondition = "WHERE donated_on BETWEEN ? AND ?";
            }
        }
    
        // Construct the SQL query
        $query = "SELECT d_name, amount, description, donated_on FROM donation $dateCondition";
    
        // Prepare the statement
        $stmt = $this->conn->prepare($query);
    
        // Check if the statement was prepared successfully
        if ($stmt === false) {
            die('MySQL prepare error: ' . $this->conn->error);
        }
    
        // Bind the parameters if a date condition is set
        if ($dateCondition) {
            $stmt->bind_param('ss', $startDate, $endDate); // Bind the start and end date parameters as strings
        }
    
        // Execute the statement
        $stmt->execute();
    
        // Get the result
        $result = $stmt->get_result(); // Get the result set from the prepared statement
    
        // Fetch all results as an associative array
        return $result->fetch_all(MYSQLI_ASSOC); // Use mysqli's fetch_all method
    }
    

    
    
    public function generateAppointmentReport($type = null, $days = null) {
        // Start building the base query
        $sql = "
            SELECT 'RequestForm' AS type, 
                   r.req_person AS fullnames, 
                   r.req_category AS Event_Name, 
                   a.payable_amount, 
                   r.role AS roles, 
                   a.paid_date 
            FROM req_form r
            JOIN appointment_schedule a ON r.req_id = a.request_id
            WHERE r.status = 'Approved' AND (a.status = 'Completed' AND a.p_status = 'Paid')
        ";
    
        $sql .= "
            UNION ALL
            SELECT 'Baptism' AS type, 
                   b.fullname AS fullnames, 
                   b.event_name AS Event_Name, 
                   a.payable_amount, 
                   b.role AS roles, 
                   a.paid_date 
            FROM baptismfill b
            JOIN appointment_schedule a ON b.baptism_id = a.baptismfill_id
            WHERE b.status = 'Approved' AND (a.status = 'Completed' AND a.p_status = 'Paid')
        ";
    
        $sql .= "
            UNION ALL
            SELECT 'Confirmation' AS type, 
                   c.fullname AS fullnames, 
                   c.event_name AS Event_Name, 
                   a.payable_amount, 
                   c.role AS roles, 
                   a.paid_date 
            FROM confirmationfill c
            JOIN appointment_schedule a ON c.confirmationfill_id = a.confirmation_id
            WHERE c.status = 'Approved' AND (a.status = 'Completed' AND a.p_status = 'Paid')
        ";
    
        $sql .= "
            UNION ALL
            SELECT 'Marriage' AS type, 
                   m.groom_name AS fullnames, 
                   m.event_name AS Event_Name, 
                   a.payable_amount, 
                   m.role AS roles, 
                   a.paid_date 
            FROM marriagefill m
            JOIN appointment_schedule a ON m.marriagefill_id = a.marriage_id
            WHERE m.status = 'Approved' AND (a.status = 'Completed' AND a.p_status = 'Paid')
        ";
    
        // Initialize an array for WHERE conditions
        $conditions = [];
        $params = []; // Array to hold the parameters for binding
    
        // Apply the type filter if it’s set
        if ($type && $type !== 'All') {
            $conditions[] = "type = ?";
            $params[] = $type; // Add the type to the parameters
        }
    
        // Apply the date range filter if provided
        if ($days) {
            $dateCondition = date('Y-m-d', strtotime("-$days days"));
            $conditions[] = "paid_date >= ?";
            $params[] = $dateCondition; // Add the date condition to the parameters
        }
    
        // Append the conditions to the main SQL query
        if ($conditions) {
            $sql = "SELECT * FROM ({$sql}) AS main_report WHERE " . implode(" AND ", $conditions);
        }
    
        // Prepare the statement
        $stmt = $this->conn->prepare($sql);
    
        // Dynamically bind the parameters
        if ($params) {
            // Create a string for the types of parameters
            $types = str_repeat('s', count($params)); // Assuming all parameters are strings
            $stmt->bind_param($types, ...$params); // Bind the parameters
        }
    
        // Execute and return the results
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Fetch results as associative array
    }
    
    public function getTotalPayableAmount() {
        // Query to calculate the total payable amount across all appointments
        $sql = "SELECT SUM(payable_amount) AS total_payable_amount 
                FROM (
                    SELECT a.payable_amount FROM req_form r
                    JOIN appointment_schedule a ON r.req_id = a.request_id WHERE r.status = 'Approved' AND (a.status = 'Completed' OR a.p_status = 'Paid')
                    UNION ALL
                    SELECT a.payable_amount FROM confirmationfill cf
                    JOIN appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id WHERE cf.status = 'Approved' AND (a.status = 'Completed' OR a.p_status = 'Paid')
                    UNION ALL
                    SELECT a.payable_amount FROM baptismfill b
                    JOIN appointment_schedule a ON b.baptism_id = a.baptismfill_id WHERE b.status = 'Approved' AND (a.status = 'Completed' OR a.p_status = 'Paid')
                    UNION ALL
                    SELECT a.payable_amount FROM marriagefill mf
                    JOIN appointment_schedule a ON mf.marriagefill_id = a.marriage_id WHERE mf.status = 'Approved' AND (a.status = 'Completed' OR a.p_status = 'Paid')
                    UNION ALL
                    SELECT a.payable_amount FROM defuctomfill df
                    JOIN appointment_schedule a ON df.defuctomfill_id = a.defuctom_id WHERE df.status = 'Approved' AND (a.status = 'Completed' OR a.p_status = 'Paid')
                ) AS all_payable_amounts";
        
        $result = $this->conn->query($sql);
        
        if ($result) {
            $data = $result->fetch_assoc();
            $totalPayableAmount = $data['total_payable_amount'] ?? 0; // Default to 0 if no result
        } else {
            echo "Error: " . $this->conn->error;
            $totalPayableAmount = 0;
        }
    
        return $totalPayableAmount; // Return the total payable amount
    }
    
    public function getDonations() {
        $sql = "SELECT `donation_id`, `d_name`, `amount`, `donated_on`, `description` FROM `donation`";
        $result = $this->conn->query($sql);

        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }

        $donations = [];
        while ($row = $result->fetch_assoc()) {
            $donations[] = $row;
        }
        return $donations;
    }

    public function getDonationsTotal() {
        // Query to get the total amount and donation records
        $sql = "SELECT `donation_id`, `d_name`, `amount`, `donated_on`, `description`, 
                       (SELECT SUM(amount) FROM donation) AS total_amount 
                FROM `donation`";
        $result = $this->conn->query($sql);
    
        if ($result === FALSE) {
            die("Error: " . $this->conn->error);
        }
    
        $donations = [];
        while ($row = $result->fetch_assoc()) {
            $donations[] = $row;
        }
    
        // Retrieve the total amount (if there are any donations)
        $totalAmount = count($donations) > 0 ? $donations[0]['total_amount'] : 0;
    
        return ['donations' => $donations, 'total_amount' => $totalAmount];
    }
    
    public function getRequestAppointments() {
        $sql = "SELECT 
         'RequestForm' AS type,
         r.status AS status,
         r.req_person AS fullnames,
         r.req_id AS id,
            r.role AS roles,
            r.req_category AS Event_Name,
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            a.appsched_id,
            a.baptismfill_id,
            a.schedule_id AS appointment_schedule_id,
            a.payable_amount AS payable_amount,
            a.status AS c_status,
            a.p_status AS p_status,
            r.created_at AS r_created_at
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
 
    r.status = 'Approved' AND 
    (a.status = 'Completed' OR a.p_status = 'Paid')

 ";
    
        return $this->fetchAppointments($sql);
    }
    public function getConfirmationAppointments() {
        $sql = "SELECT 
         'Confirmation' AS type,
         cf.status AS status,
            cf.fullname AS fullnames,
    
            cf.confirmationfill_id AS id,
            cf.role AS roles,
            cf.c_date_birth AS birth,
            cf.c_age AS age,
            
            cf.event_name AS Event_Name,
           
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            a.appsched_id,
            a.baptismfill_id,
          
       
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
    
      
        WHERE 
 
    cf.status = 'Approved' AND 
    (a.status = 'Completed' OR a.p_status = 'Paid')
    UNION 
    SELECT 
         'Confirmation' AS type,
         cf.status AS status,
            cf.fullname AS fullnames,
    
            cf.confirmationfill_id AS id,
            cf.role AS roles,
            cf.c_date_birth AS birth,
            cf.c_age AS age,
            
            cf.event_name AS Event_Name,
           
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            a.appsched_id,
            a.baptismfill_id,
          
       
            a.schedule_id AS appointment_schedule_id,
            a.payable_amount AS payable_amount,
            a.status AS c_status,
            a.p_status AS p_status,
            
            cf.c_created_at 
            FROM 
                    confirmationfill cf
                JOIN 
                    announcement an ON cf.announcement_id = an.announcement_id
                JOIN 
                    appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
                JOIN 
                    schedule s ON an.schedule_id = s.schedule_id   
            WHERE 
 
 cf.status = 'Approved' AND 
 (a.status = 'Completed' OR a.p_status = 'Paid')

 ";
    
        return $this->fetchAppointments($sql);
    }
    public function getBaptismAppointments() {
        $sql = "SELECT 
        a.paid_date AS p_date,
         'Baptism' AS type,
         b.status AS status,
            b.fullname AS fullnames,
           
            b.baptism_id AS id,
            b.role AS roles,
            
            b.event_name AS Event_Name,
           
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            a.appsched_id,
            a.baptismfill_id,
           
          
            a.schedule_id AS appointment_schedule_id,
            a.payable_amount AS payable_amount,
            a.status AS c_status,
            a.p_status AS p_status,
            b.created_at 
        FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
        JOIN baptismfill b ON s.schedule_id = b.schedule_id
        JOIN appointment_schedule a ON b.baptism_id = a.baptismfill_id
        JOIN schedule sch ON a.schedule_id = sch.schedule_id  
        WHERE 
    b.status = 'Approved' AND 
    (a.status = 'Completed' OR a.p_status = 'Paid')
UNION 

SELECT 
a.paid_date AS p_date,
         'Baptism' AS type,
         b.status AS status,
            b.fullname AS fullnames,
           
            b.baptism_id AS id,
            b.role AS roles,
            
            b.event_name AS Event_Name,
          
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            a.appsched_id,
            a.baptismfill_id,
           
          
            a.schedule_id AS appointment_schedule_id,
            a.payable_amount AS payable_amount,
            a.status AS c_status,
            a.p_status AS p_status,
         
            b.created_at 
            FROM 
                baptismfill b
            JOIN 
                announcement an ON b.announcement_id = an.announcement_id
            JOIN 
                appointment_schedule a ON b.baptism_id = a.baptismfill_id
            JOIN 
                schedule s ON an.schedule_id = s.schedule_id
                WHERE 
    b.status = 'Approved' AND 
    (a.status = 'Completed' OR a.p_status = 'Paid')
 ";
    
        return $this->fetchAppointments($sql);
    }
    
    public function getMarriageAppointments() {
        $sql = "SELECT 
             'Marriage' AS type,
           mf.status AS status,
            mf.groom_name AS fullnames,
           
            mf.marriagefill_id AS id,
            mf.role AS roles,
            mf.event_name AS Event_Name,
           
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            a.appsched_id,
            a.marriage_id,
            a.schedule_id AS appointment_schedule_id,
            a.payable_amount AS payable_amount,
            a.status AS c_status,
            a.p_status AS p_status,
          
            mf.m_created_at 
        FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
        JOIN marriagefill mf ON s.schedule_id = mf.schedule_id
        JOIN appointment_schedule a ON mf.marriagefill_id = a.marriage_id
     LEFT   JOIN schedule sch ON a.schedule_id = sch.schedule_id
        WHERE 
    mf.status = 'Approved' AND 
    (a.status = 'Completed' OR a.p_status = 'Paid')
    UNION 
    SELECT 
             'Marriage' AS type,
           mf.status AS status,
            mf.groom_name AS fullnames,
           
            mf.marriagefill_id AS id,
            mf.role AS roles,
            mf.event_name AS Event_Name,
          
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            a.appsched_id,
            a.marriage_id,
            a.schedule_id AS appointment_schedule_id,
            a.payable_amount AS payable_amount,
            a.status AS c_status,
            a.p_status AS p_status,
    
            mf.m_created_at 
            FROM 
                marriagefill mf
            JOIN 
                announcement an ON mf.announcement_id = an.announcement_id
            JOIN 
                appointment_schedule a ON mf.marriagefill_id = a.marriage_id
            JOIN 
                schedule s ON an.schedule_id = s.schedule_id
            WHERE 
    mf.status = 'Approved' AND 
    (a.status = 'Completed' OR a.p_status = 'Paid')
    
    ";
    
        return $this->fetchAppointments($sql);
    }public function getDefuctomAppointments() {
        $sql = "SELECT 
        'Defuctom' AS type,
          df.status AS status,
            df.d_fullname AS fullnames,
          
            df.defuctomfill_id AS id,
            df.role AS roles,
            df.event_name AS Event_Name,
            c.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            a.appsched_id,
            a.defuctom_id,  
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

        WHERE 
    
    df.status = 'Approved' AND 
    (a.status = 'Completed' OR a.p_status = 'Paid') ";
    
        return $this->fetchAppointments($sql);
    }private function fetchAppointments($sql) {
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

    public function getPendingAppointments() {
        $RequestAppointments = $this->getRequestAppointments();
        $confirmationAppointments = $this->getConfirmationAppointments();
        $baptismAppointments = $this->getBaptismAppointments();
        $marriageAppointments = $this->getMarriageAppointments();
        $defuctomAppointments = $this->getDefuctomAppointments();
    
        // Combine all appointments into one array
        $allAppointments = array_merge( $RequestAppointments,$confirmationAppointments,$baptismAppointments, $marriageAppointments, $defuctomAppointments);
    
        // Sort all appointments based on the correct created_at timestamp for each event type
        usort($allAppointments, function($a, $b) {
            // Determine the correct created_at field for each event type
            $createdAtFieldA = $a['type'] === 'Confirmation' ? $a['c_created_at'] :
                                 ($a['type'] === 'Baptism' ? $a['created_at'] :
                                 ($a['type'] === 'RequestForm' ? $a['r_created_at'] :
                               ($a['type'] === 'Marriage' ? $a['m_created_at'] :
                               
                               ($a['type'] === 'Defuctom' ? $a['d_created_at'] : '0')))); // Adjust based on your actual fields
    
            $createdAtFieldB =  $b['type'] === 'Confirmation' ? $b['c_created_at'] :
                                ($b['type'] === 'Baptism' ? $b['created_at'] :
                                ($a['type'] === 'RequestForm' ? $a['r_created_at'] :
                               ($b['type'] === 'Marriage' ? $b['m_created_at'] :
                               ($b['type'] === 'Defuctom' ? $b['d_created_at'] : '0')))); // Adjust based on your actual fields
    
            // Convert created_at timestamps to UNIX timestamps for comparison
            $aCreatedAt = strtotime($createdAtFieldA ?? '0');
            $bCreatedAt = strtotime($createdAtFieldB ?? '0');
    
            // First, sort by created_at timestamp (ascending order)
            if ($aCreatedAt !== $bCreatedAt) {
                return $aCreatedAt - $bCreatedAt; // Ascending order
            }
    
            // If created_at timestamps are the same, then sort by event type
            $eventOrder = ['Baptism','RequestForm','Confirmation', 'Marriage', 'Defuctom'];
            return array_search($a['type'], $eventOrder) - array_search($b['type'], $eventOrder);
        });
    
        return $allAppointments;
    }
    public function getBaptismRecordById($baptismId) {
        // Combined SQL query using UNION for a single record
        $sql = "SELECT 
                    s.date AS scheduleDate,
                    b.baptism_id AS id,
                    b.event_name AS Event_Name,
                    b.fullname AS citizen_name,
                    b.gender AS gender,
                    b.address AS address,
                    b.c_date_birth AS birth_date,
                    b.age AS age,
                    b.father_fullname AS father_name,
                    b.pbirth AS place_of_birth,
                    b.mother_fullname AS mother_name,
                    b.religion AS religion,
                    b.parent_resident AS parent_residence,
                    b.godparent AS godparent,
                    b.status AS citizen_status
                FROM 
                    schedule s
                LEFT JOIN 
                    citizen c ON c.citizend_id = s.citizen_id 
                JOIN 
                    baptismfill b ON s.schedule_id = b.schedule_id
                JOIN 
                    appointment_schedule a ON b.baptism_id = a.baptismfill_id
                WHERE 
                    a.p_status = 'Paid' AND a.status = 'Completed' AND b.baptism_id = ?
                UNION
                SELECT 
                s.date AS scheduleDate,
                    b.baptism_id AS id,
                    b.event_name AS Event_Name,
                    b.fullname AS citizen_name,
                    b.gender AS gender,
                    b.address AS address,
                    b.c_date_birth AS birth_date,
                    b.age AS age,
                    b.father_fullname AS father_name,
                    b.pbirth AS place_of_birth,
                    b.mother_fullname AS mother_name,
                    b.religion AS religion,
                    b.parent_resident AS parent_residence,
                    b.godparent AS godparent,
                    b.status AS citizen_status
                FROM 
                    baptismfill b
                JOIN 
                    announcement an ON b.announcement_id = an.announcement_id
                JOIN 
                    appointment_schedule a ON b.baptism_id = a.baptismfill_id
                JOIN 
                    schedule s ON an.schedule_id = s.schedule_id
                WHERE 
                    a.p_status = 'Paid' AND a.status = 'Completed' AND b.baptism_id = ?";
    
        // Prepare and execute the SQL statement
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $baptismId, $baptismId); // Bind the ID parameter for both parts of the UNION
        $stmt->execute();
    
        // Fetch the single result
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Return only one record
    }
    public function getTotalBaptisms() {
        $sql = "SELECT COUNT(*) as total 
                FROM (
                    SELECT b.baptism_id 
                    FROM schedule s
                    LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
                    JOIN baptismfill b ON s.schedule_id = b.schedule_id
                    JOIN appointment_schedule a ON b.baptism_id = a.baptismfill_id
                    WHERE a.p_status = 'Paid' AND a.status = 'Completed'
                    
                    UNION
                    
                    SELECT b.baptism_id 
                    FROM baptismfill b
                    JOIN announcement an ON b.announcement_id = an.announcement_id
                    JOIN appointment_schedule a ON b.baptism_id = a.baptismfill_id
                    JOIN schedule s ON an.schedule_id = s.schedule_id
                    WHERE a.p_status = 'Paid' AND a.status = 'Completed'
                ) as completed_baptisms";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    public function getBaptismRecords() {
        // Combined SQL query using UNION
        $sql = "SELECT 
                    b.baptism_id AS id,
                    b.event_name AS Event_Name,
                    b.fullname AS citizen_name,
                    b.gender AS gender,
                    b.address AS address,
                    b.c_date_birth AS birth_date,
                    b.age AS age,
                    b.father_fullname AS father_name,
                    b.pbirth AS place_of_birth,
                    b.mother_fullname AS mother_name,
                    b.religion AS religion,
                    b.parent_resident AS parent_residence,
                    b.godparent AS godparent,
                    b.status AS citizen_status
                    FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
        JOIN baptismfill b ON s.schedule_id = b.schedule_id
                JOIN 
                    appointment_schedule a ON b.baptism_id = a.baptismfill_id
                WHERE 
                    a.p_status = 'Paid' AND a.status = 'Completed'
                UNION
                SELECT 
                    b.baptism_id AS id,
                    b.event_name AS Event_Name,
                    b.fullname AS citizen_name,
                    b.gender AS gender,
                    b.address AS address,
                    b.c_date_birth AS birth_date,
                    b.age AS age,
                    b.father_fullname AS father_name,
                    b.pbirth AS place_of_birth,
                    b.mother_fullname AS mother_name,
                    b.religion AS religion,
                    b.parent_resident AS parent_residence,
                    b.godparent AS godparent,
                    b.status AS citizen_status
                    FROM 
                baptismfill b
            JOIN 
                announcement an ON b.announcement_id = an.announcement_id
            JOIN 
                appointment_schedule a ON b.baptism_id = a.baptismfill_id
            JOIN 
                schedule s ON an.schedule_id = s.schedule_id
                WHERE 
                    a.p_status = 'Paid' AND a.status = 'Completed'";

        // Prepare and execute the SQL statement
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        // Fetch the results
        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);

        // Return the records as an associative array
        return $records;
    }
    public function getConfirmationRecordById($confirmationId) {
        // SQL query to retrieve a single confirmation record by ID, using UNION
        $sql = "SELECT 
         cf.confirmationfill_id AS id,
                    cf.fullname AS fullname,
                    cf.c_date_birth AS dob,
                    cf.event_name AS Event_Name,
                    cf.c_address AS address,
                    cf.c_gender AS gender,
                    cf.c_age AS age,
                    cf.date_of_baptism AS date_of_baptism,
                    cf.name_of_church AS name_of_church,
                    cf.father_fullname AS father_fullname,
                    cf.mother_fullname AS mother_fullname,
                    cf.permission_to_confirm AS permission_to_confirm,
                    cf.church_address AS church_address,
                    s.date AS confirmation_date
                FROM 
                    schedule s
                LEFT JOIN 
                    citizen c ON c.citizend_id = s.citizen_id 
                JOIN 
                    confirmationfill cf ON s.schedule_id = cf.schedule_id
                JOIN 
                    appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
                WHERE 
                    a.p_status = 'Paid' AND a.status = 'Completed' AND cf.confirmationfill_id = ?
                UNION
                SELECT 
                cf.confirmationfill_id AS id,
                    cf.fullname AS fullname,
                    cf.c_date_birth AS dob,
                    cf.event_name AS Event_Name,
                    cf.c_address AS address,
                    cf.c_gender AS gender,
                    cf.c_age AS age,
                    cf.date_of_baptism AS date_of_baptism,
                    cf.name_of_church AS name_of_church,
                    cf.father_fullname AS father_fullname,
                    cf.mother_fullname AS mother_fullname,
                    cf.permission_to_confirm AS permission_to_confirm,
                    cf.church_address AS church_address,
                    s.date AS confirmation_date
                FROM 
                    confirmationfill cf
                JOIN 
                    announcement an ON cf.announcement_id = an.announcement_id
                JOIN 
                    appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
                JOIN 
                    schedule s ON an.schedule_id = s.schedule_id   
                WHERE 
                    a.p_status = 'Paid' AND a.status = 'Completed' AND cf.confirmationfill_id = ?";
    
        // Prepare and execute the SQL statement
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $confirmationId, $confirmationId); // Bind the ID parameter for both parts of the UNION
        $stmt->execute();
    
        // Fetch the single result
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Return only one record as an associative array
    }
    
    public function getConfirmationRecords() {
        // SQL query without year filtering
        $sql = "SELECT 
             cf.confirmationfill_id AS id,
        cf.fullname AS fullname,
        cf.c_date_birth AS dob,
        cf.event_name AS Event_Name,
        cf.c_address AS address,
        cf.c_gender AS gender,
        cf.c_age AS age,
        cf.date_of_baptism AS date_of_baptism,
        cf.name_of_church AS name_of_church,
        cf.father_fullname AS father_fullname,
        cf.mother_fullname AS mother_fullname,
        cf.permission_to_confirm AS permission_to_confirm,
        cf.church_address AS church_address,
        s.date AS confirmation_date
        FROM 
        schedule s
    LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
    JOIN confirmationfill cf ON s.schedule_id = cf.schedule_id
    JOIN 
        appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
    WHERE 
        a.p_status = 'Paid' AND a.status = 'Completed'
                    
                    UNION

                    SELECT 
                    cf.confirmationfill_id AS id,
                    cf.fullname AS fullname,
        cf.c_date_birth AS dob,
        cf.event_name AS Event_Name,
        cf.c_address AS address,
        cf.c_gender AS gender,
        cf.c_age AS age,
        cf.date_of_baptism AS date_of_baptism,
        cf.name_of_church AS name_of_church,
        cf.father_fullname AS father_fullname,
        cf.mother_fullname AS mother_fullname,
        cf.permission_to_confirm AS permission_to_confirm,
        cf.church_address AS church_address,
        s.date AS confirmation_date
        FROM 
                confirmationfill cf
            JOIN 
                announcement an ON cf.announcement_id = an.announcement_id
            JOIN 
                appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
            JOIN 
                schedule s ON an.schedule_id = s.schedule_id   
                WHERE 
                a.p_status = 'Paid' AND a.status = 'Completed'

                    
                    ";

        // Prepare and execute the SQL statement
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        // Fetch the results
        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);

        // Return the records as an associative array
        return $records;
    }
    public function getTotalConfirmationsDone() {
        $sql = "SELECT COUNT(*) AS total FROM (
                    SELECT cf.confirmationfill_id 
                    FROM schedule s
                    LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
                    JOIN confirmationfill cf ON s.schedule_id = cf.schedule_id
                    JOIN appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
                    WHERE a.p_status = 'Paid' AND a.status = 'Completed'
                    
                    UNION
    
                    SELECT cf.confirmationfill_id 
                    FROM confirmationfill cf
                    JOIN announcement an ON cf.announcement_id = an.announcement_id
                    JOIN appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
                    JOIN schedule s ON an.schedule_id = s.schedule_id
                    WHERE a.p_status = 'Paid' AND a.status = 'Completed'
                ) AS completed_confirmations";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }
    

    public function getDefunctorumRecords() {
        $sql = "SELECT 
          df.defuctomfill_id AS id ,
        df.d_fullname AS fullname,
        df.d_gender AS gender,
        df.event_name AS Event_Name,
        df.cause_of_death AS cause_of_death,
        df.marital_status AS marital_status,
        df.place_of_birth AS place_of_birth,
        df.father_fullname AS father_fullname,
        df.date_of_birth AS date_of_birth,
        df.age AS age,
        df.mother_fullname AS mother_fullname,
        df.parents_residence AS parents_residence,

        df.d_address AS address,
        df.date_of_death AS date_of_death,
        df.place_of_death AS place_of_death
        FROM 
        schedule s
    LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
    JOIN defuctomfill df ON s.schedule_id = df.schedule_id
    JOIN 
        appointment_schedule a ON df.defuctomfill_id = a.defuctom_id
    WHERE 
        a.p_status = 'Paid' AND a.status = 'Completed'
    
";





        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);
        return $records;
    }
    public function getTotalDefunctorumDone() {
        $sql = "SELECT COUNT(*) AS total 
                FROM defuctomfill df
                JOIN appointment_schedule a ON df.defuctomfill_id = a.defuctom_id
                WHERE a.p_status = 'Paid' AND a.status = 'Completed'";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }
    
    public function getDefunctorumRecordById($defunctorumId) {
        // SQL query to fetch a specific defunctorum record by ID
        $sql = "SELECT 
                    df.defuctomfill_id AS id ,
                    df.d_fullname AS fullname,
                    df.d_gender AS gender,
                    df.event_name AS Event_Name,
                    df.cause_of_death AS cause_of_death,
                    df.marital_status AS marital_status,
                    df.place_of_birth AS place_of_birth,
                    df.father_fullname AS father_fullname,
                    df.date_of_birth AS date_of_birth,
                    df.age AS age,
                    df.mother_fullname AS mother_fullname,
                    df.parents_residence AS parents_residence,
                    df.d_address AS address,
                    df.date_of_death AS date_of_death,
                    df.place_of_death AS place_of_death
                FROM 
                    defuctomfill df
                JOIN 
                    appointment_schedule a ON df.defuctomfill_id = a.defuctom_id
                WHERE 
                    df.defuctomfill_id = ? AND a.p_status = 'Paid' AND a.status = 'Completed'";
    
        // Prepare and execute the SQL statement
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $defunctorumId); // Bind the ID parameter
        $stmt->execute();
    
        // Fetch the result
        $result = $stmt->get_result();
        $record = $result->fetch_assoc(); // Fetch a single record as an associative array
    
        // Return the record
        return $record;
    }
    

    public function getWeddingRecords() {
        $sql="SELECT 
        mf.marriagefill_id AS id,
       mf.event_name AS Event_Name,
       CONCAT(mf.groom_name, ' & ', mf.bride_name) AS groom_and_bride,

        mf.groom_dob AS groom_dob,
        mf.groom_place_of_birth AS groom_place_of_birth,
        mf.groom_citizenship AS groom_citizenship,
        mf.groom_address AS groom_address,
        mf.groom_religion AS groom_religion,
        mf.groom_previously_married AS groom_previously_married,
       
        mf.bride_dob AS bride_dob,
        mf.bride_place_of_birth AS bride_place_of_birth,
        mf.bride_citizenship AS bride_citizenship,
        mf.bride_address AS bride_address,
        mf.bride_religion AS bride_religion,
        mf.bride_previously_married AS bride_previously_married,
        s.date AS s_date
        FROM 
        schedule s
    LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
    JOIN marriagefill mf ON s.schedule_id = mf.schedule_id
    JOIN 
        appointment_schedule a ON mf.marriagefill_id = a.marriage_id
    WHERE 
        a.p_status = 'Paid' AND a.status = 'Completed'
        
        UNION

        SELECT 
        mf.marriagefill_id AS id,
        mf.event_name AS Event_Name,
        CONCAT(mf.groom_name, ' & ', mf.bride_name) AS groom_and_bride,
        mf.groom_dob AS groom_dob,
        mf.groom_place_of_birth AS groom_place_of_birth,
        mf.groom_citizenship AS groom_citizenship,
        mf.groom_address AS groom_address,
        mf.groom_religion AS groom_religion,
        mf.groom_previously_married AS groom_previously_married,
 
        mf.bride_dob AS bride_dob,
        mf.bride_place_of_birth AS bride_place_of_birth,
        mf.bride_citizenship AS bride_citizenship,
        mf.bride_address AS bride_address,
        mf.bride_religion AS bride_religion,
        mf.bride_previously_married AS bride_previously_married,
        s.date AS s_date
        FROM 
                marriagefill mf
            JOIN 
                announcement an ON mf.announcement_id = an.announcement_id
            JOIN 
                appointment_schedule a ON mf.marriagefill_id = a.marriage_id
            JOIN 
                schedule s ON an.schedule_id = s.schedule_id
    WHERE 
        a.p_status = 'Paid' AND a.status = 'Completed'
        
        ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $records = $result->fetch_all(MYSQLI_ASSOC);
    return $records;
}
public function getTotalWeddingRecords() {
    $sql = "SELECT 
                COUNT(*) AS total
            FROM 
                marriagefill mf
            JOIN 
                appointment_schedule a ON mf.marriagefill_id = a.marriage_id
            JOIN 
                schedule s ON mf.schedule_id = s.schedule_id
            WHERE 
                a.p_status = 'Paid' AND a.status = 'Completed'
            
            UNION ALL

            SELECT 
                COUNT(*) AS total
            FROM 
                marriagefill mf
            JOIN 
                announcement an ON mf.announcement_id = an.announcement_id
            JOIN 
                appointment_schedule a ON mf.marriagefill_id = a.marriage_id
            JOIN 
                schedule s ON an.schedule_id = s.schedule_id
            WHERE 
                a.p_status = 'Paid' AND a.status = 'Completed'";

    // Prepare and execute the SQL statement
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    
    // Fetch results
    $result = $stmt->get_result();
    $totals = $result->fetch_all(MYSQLI_ASSOC);

    // Sum the total from both sources
    $totalWeddings = array_sum(array_column($totals, 'total'));

    return $totalWeddings;
}

public function getWeddingRecordById($weddingId) {
    $sql = "SELECT 
      priest.fullname AS priest_name,
            mf.marriagefill_id AS id,
            mf.event_name AS Event_Name,
            CONCAT(mf.groom_name, ' & ', mf.bride_name) AS groom_and_bride,
            mf.groom_dob AS groom_dob,
            mf.groom_place_of_birth AS groom_place_of_birth,
            mf.groom_citizenship AS groom_citizenship,
            mf.groom_address AS groom_address,
            mf.groom_religion AS groom_religion,
            mf.groom_previously_married AS groom_previously_married,
            mf.bride_dob AS bride_dob,
            mf.bride_place_of_birth AS bride_place_of_birth,
            mf.bride_citizenship AS bride_citizenship,
            mf.bride_address AS bride_address,
            mf.bride_religion AS bride_religion,
            mf.bride_previously_married AS bride_previously_married,
            s.date AS s_date
            
        FROM 
            marriagefill mf
        JOIN 
            schedule s ON s.schedule_id = mf.schedule_id
        JOIN 
            appointment_schedule a ON mf.marriagefill_id = a.marriage_id
            JOIN priest_approval pa ON pa.approval_id = mf.approval_id
    LEFT JOIN citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'
 
        WHERE 
            mf.marriagefill_id = ? AND a.p_status = 'Paid' AND a.status = 'Completed'
        
        UNION
        
        SELECT 
        priest.fullname AS priest_name,
            mf.marriagefill_id AS id,
            mf.event_name AS Event_Name,
            CONCAT(mf.groom_name, ' & ', mf.bride_name) AS groom_and_bride,
            mf.groom_dob AS groom_dob,
            mf.groom_place_of_birth AS groom_place_of_birth,
            mf.groom_citizenship AS groom_citizenship,
            mf.groom_address AS groom_address,
            mf.groom_religion AS groom_religion,
            mf.groom_previously_married AS groom_previously_married,
            mf.bride_dob AS bride_dob,
            mf.bride_place_of_birth AS bride_place_of_birth,
            mf.bride_citizenship AS bride_citizenship,
            mf.bride_address AS bride_address,
            mf.bride_religion AS bride_religion,
            mf.bride_previously_married AS bride_previously_married,
            s.date AS s_date
        FROM 
            marriagefill mf
        JOIN 
            announcement an ON mf.announcement_id = an.announcement_id
        JOIN 
            appointment_schedule a ON mf.marriagefill_id = a.marriage_id
        JOIN 
            schedule s ON an.schedule_id = s.schedule_id
            JOIN priest_approval pa ON pa.approval_id = an.approval_id
    LEFT JOIN citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest' AND priest.r_status = 'Active'

        WHERE 
            mf.marriagefill_id = ? AND a.p_status = 'Paid' AND a.status = 'Completed'";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $weddingId, $weddingId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}


}
    ?>