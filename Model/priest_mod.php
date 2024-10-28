<?php
class Priest {
    private $conn;
    private $regId;
  public function __construct($conn, $regId = null) {
        $this->conn = $conn;
        $this->regId = $regId;

        // Call the update function upon class initialization
        $this->updatePendingAppointments();
    }

    // Method to get appointments for a specific priest
    public function getPriestAppointmentSchedule($priestId) {
        // Retrieve the appointments for the specific priest
        $sql = "
        SELECT 
            'baptism' AS type,
            b.baptism_id AS id,
            b.role AS roles,
            b.event_name AS Event_Name,
            c.fullname AS citizen_name, 
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            pa.approval_id,
            priest.fullname AS priest_name
        FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
        JOIN baptismfill b ON s.schedule_id = b.schedule_id
        LEFT JOIN 
            priest_approval pa ON pa.approval_id = b.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
        WHERE pa.priest_id = ? 
            AND pa.pr_status = 'Pending'
        UNION ALL
        SELECT 
            'confirmation' AS type,
            cf.confirmationfill_id AS id,
            cf.role AS roles,
            cf.event_name AS Event_Name,
            c.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            pa.approval_id,
            priest.fullname AS priest_name
        FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
        JOIN confirmationfill cf ON s.schedule_id = cf.schedule_id
        LEFT JOIN 
            priest_approval pa ON pa.approval_id = cf.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
        WHERE pa.priest_id = ?
            AND pa.pr_status = 'Pending'
        UNION ALL
        SELECT 
            'marriage' AS type,
            mf.marriagefill_id AS id,
            mf.role AS roles,
            mf.event_name AS Event_Name,
            c.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            pa.approval_id,
            priest.fullname AS priest_name
        FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
        JOIN marriagefill mf ON s.schedule_id = mf.schedule_id
        LEFT JOIN 
            priest_approval pa ON pa.approval_id = mf.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
        WHERE pa.priest_id = ?
            AND pa.pr_status = 'Pending'
        UNION ALL
        SELECT 
            'defuctom' AS type,
            df.defuctomfill_id AS id,
            df.role AS roles,
            df.event_name AS Event_Name,
            c.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            pa.approval_id,
            priest.fullname AS priest_name
        FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
        JOIN defuctomfill df ON s.schedule_id = df.schedule_id
        LEFT JOIN 
            priest_approval pa ON pa.approval_id = df.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
        WHERE pa.priest_id = ?
            AND pa.pr_status = 'Pending'
        UNION ALL
        SELECT 
            'requestform' AS type,
            rf.req_id AS id,
            rf.role AS roles,
            rf.req_category AS Event_Name,
            rf.req_person AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            pa.approval_id,
            priest.fullname AS priest_name
        FROM 
            schedule s
        LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
        JOIN req_form rf ON s.schedule_id = rf.schedule_id
        LEFT JOIN 
            priest_approval pa ON pa.approval_id = rf.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
        WHERE pa.priest_id = ?
            AND pa.pr_status = 'Pending'
        ORDER BY schedule_date ASC
        ";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiiii", $priestId, $priestId, $priestId, $priestId, $priestId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $appointments = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
        }
    
        $stmt->close();
    
        return $appointments;
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
    
    public function approveAppointment($appointmentId, $appointmentType) {
        // Determine the correct table and ID field based on the appointment type
        switch ($appointmentType) {
            case 'baptism':
                $table = 'baptismfill';
                $idField = 'baptism_id';
                break;
            case 'confirmation':
                $table = 'confirmationfill';
                $idField = 'confirmationfill_id';
                break;
            case 'defuctom':
                $table = 'defuctomfill';
                $idField = 'defuctomfill_id';
                break;
            case 'marriage':
                $table = 'marriagefill';
                $idField = 'marriagefill_id';
                break;
            case 'requestform':
                $table = 'req_form';
                $idField = 'req_id';
                break;
            default:
                return false; // Invalid appointment type
        }
    
        // Query to get the approval_id associated with the appointment
        $getApprovalIdQuery = "SELECT approval_id FROM $table WHERE $idField = ?";
        if ($stmt = $this->conn->prepare($getApprovalIdQuery)) {
            $stmt->bind_param("i", $appointmentId);
            $stmt->execute();
            $stmt->bind_result($approvalId);
            $stmt->fetch();
            $stmt->close();
    
            if ($approvalId) {
                // Prepare the SQL query to update the pr_status in the priest_approval table
                $query = "UPDATE priest_approval SET pr_status = 'Approved' WHERE approval_id = ?";
                if ($stmt = $this->conn->prepare($query)) {
                    $stmt->bind_param("i", $approvalId);
                    if ($stmt->execute()) {
                        $stmt->close();
                        return true; // Return true on success
                    } else {
                        $stmt->close();
                        return false; // Return false on failure
                    }
                }
            } else {
                return false; // Return false if no approval_id is found
            }
        } else {
            return false; // Return false if query preparation fails
        }
    }
    
    
    public function declineAppointment($appointmentId, $appointmentType, $reason) {
        // Determine the correct table and ID field based on the appointment type
        switch ($appointmentType) {
            case 'baptism':
                $table = 'baptismfill';
                $idField = 'baptism_id';
                break;
            case 'confirmation':
                $table = 'confirmationfill';
                $idField = 'confirmationfill_id';
                break;
            case 'defuctom':
                $table = 'defuctomfill';
                $idField = 'defuctomfill_id';
                break;
            case 'marriage':
                $table = 'marriagefill';
                $idField = 'marriagefill_id';
                break;
            case 'requestform':
                $table = 'req_form';
                $idField = 'req_id';
                break;
            default:
                return false; // Invalid appointment type
        }
    
        // Query to get the approval_id associated with the appointment
        $getApprovalIdQuery = "SELECT approval_id FROM $table WHERE $idField = ?";
        if ($stmt = $this->conn->prepare($getApprovalIdQuery)) {
            $stmt->bind_param("i", $appointmentId);
            $stmt->execute();
            $stmt->bind_result($approvalId);
            $stmt->fetch();
            $stmt->close();
    
            if ($approvalId) {
                // Update the pr_status and pr_description in the priest_approval table
                $query = "UPDATE priest_approval SET pr_status = 'Declined', pr_reason = ? WHERE approval_id = ?";
                if ($stmt = $this->conn->prepare($query)) {
                    $stmt->bind_param("si", $reason, $approvalId);
                    if ($stmt->execute()) {
                        $stmt->close();
                        return true; // Return true on success
                    } else {
                        $stmt->close();
                        return false; // Return false on failure
                    }
                }
            } else {
                return false; // Return false if no approval_id is found
            }
        } else {
            return false; // Return false if query preparation fails
        }
    }   
    public function getPriestScheduleByDate($priestId, $date) {
        // SQL query to get the schedule for a specific priest to approve or decline
        $sql = "
        SELECT 
           
            b.baptism_id AS id,
            b.role AS roles,
            b.event_name AS Event_Name,
            c.fullname AS citizen_name, 
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            s.end_time AS schedule_end_time,
            pa.priest_id,
            priest.fullname AS priest_name
           
        FROM 
            schedule s
            LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
            JOIN baptismfill b ON s.schedule_id = b.schedule_id
         

            LEFT JOIN 
            priest_approval pa ON pa.approval_id = b.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
        WHERE 
            pa.priest_id = ?
            AND pa.pr_status = 'Approved'
            AND DATE(s.date) = ?
        
        UNION ALL
        
        SELECT 
        
            cf.confirmationfill_id AS id,
            cf.role AS roles,
            cf.event_name AS Event_Name,
            c.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            s.end_time AS schedule_end_time,
            pa.priest_id,
            priest.fullname AS priest_name
           
         
        FROM 
            schedule s
            LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
            JOIN confirmationfill cf ON s.schedule_id = cf.schedule_id
           
            LEFT JOIN 
            priest_approval pa ON pa.approval_id = cf.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
        WHERE 
        pa.priest_id = ?
        AND pa.pr_status = 'Approved'
            AND DATE(s.date) = ?
        
        UNION ALL
        
        SELECT 
          
            mf.marriagefill_id AS id,
            mf.role AS roles,
            mf.event_name AS Event_Name,
            c.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            s.end_time AS schedule_end_time,
            pa.priest_id,
            priest.fullname AS priest_name
           
        FROM 
            schedule s
            LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
            JOIN marriagefill mf ON s.schedule_id = mf.schedule_id
            LEFT JOIN 
            priest_approval pa ON pa.approval_id = mf.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
        WHERE 
        pa.priest_id = ?
        AND pa.pr_status = 'Approved'
            AND DATE(s.date) = ?
        
        UNION ALL
        
        SELECT 
         
            df.defuctomfill_id AS id,
            df.role AS roles,
            df.event_name AS Event_Name,
            c.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            s.end_time AS schedule_end_time,
            pa.priest_id,
            priest.fullname AS priest_name
           
           
        FROM 
            schedule s
            LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
            JOIN defuctomfill df ON s.schedule_id = df.schedule_id

            LEFT JOIN 
            priest_approval pa ON pa.approval_id = df.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
        WHERE 
        pa.priest_id = ?
        AND pa.pr_status = 'Approved'
            AND DATE(s.date) = ?
            

            UNION ALL
        
            SELECT 
             
                rf.req_id AS id,
                rf.role AS roles,
                rf.req_category AS Event_Name,
                c.fullname AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_time,
                s.end_time AS schedule_end_time,
                pa.priest_id,
                priest.fullname AS priest_name
               
               
            FROM 
                schedule s
                LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
                JOIN req_form rf ON s.schedule_id = rf.schedule_id
    
                LEFT JOIN 
                priest_approval pa ON pa.approval_id = rf.approval_id
            LEFT JOIN 
                citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
            WHERE 
            pa.priest_id = ?
            AND pa.pr_status = 'Approved'
                AND DATE(s.date) = ?

                UNION ALL
        
                SELECT 
                an.announcement_id AS id,
                'Inside' AS roles,
                an.event_type AS Event_Name,
                'NULL' AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_time,
                s.end_time AS schedule_end_time,
                pa.priest_id,
                priest.fullname AS priest_name
            FROM 
                schedule s
            JOIN 
                announcement an ON s.schedule_id = an.schedule_id
            LEFT JOIN 
                priest_approval pa ON pa.approval_id = an.approval_id
            LEFT JOIN 
                citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
            WHERE 
                pa.priest_id = ? 
                AND pa.pr_status = 'Approved' 
             AND DATE(s.date) = ?
                    
        UNION ALL
        
        SELECT 
         
            ms.mass_id AS id,
            'Mass' AS roles,
           ms.mass_title AS Event_Name,
            'Mass' AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_time,
            s.end_time AS schedule_end_time,
            pa.priest_id,
            priest.fullname AS priest_name
           
           
        FROM 
            schedule s
            LEFT JOIN citizen c ON c.citizend_id = s.citizen_id 
            JOIN mass_schedule ms ON s.schedule_id = ms.schedule_id

            LEFT JOIN 
            priest_approval pa ON pa.approval_id = ms.approval_id
        LEFT JOIN 
            citizen priest ON pa.priest_id = priest.citizend_id AND priest.user_type = 'Priest'
        WHERE 
        pa.priest_id = ?
        AND pa.pr_status = 'Approved'
            AND DATE(s.date) = ?
            
                
        
                ORDER BY schedule_date ASC, schedule_time ASC
        ";
        
        // Prepare and execute the statement
        $stmt = $this->conn->prepare($sql);
        // Bind priest_id and date (four times each, for each UNION)
        $stmt->bind_param("isisisisisisis", $priestId, $date, $priestId, $date, $priestId, $date, $priestId, $date,$priestId, $date,$priestId, $date,$priestId, $date);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Fetch the data into an associative array
        $appointments = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
        }
    
        // Close the statement
        $stmt->close();
    
        // Return the result set (appointments)
        return $appointments;
    }
    
}
