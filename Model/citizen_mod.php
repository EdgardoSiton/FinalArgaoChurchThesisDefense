<?php
class Citizen {
    private $conn;
    private $regId;

    public function __construct($conn, $regId = null) {
        $this->conn = $conn;
        $this->regId = $regId;
    }
    public function cfetchRequestFills($regId) {
        $query = "
            SELECT 
           
            a.payable_amount,
            a.status,
           a.appsched_id AS appointment_id,
           a.reference_number,
            r.req_person AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_start_time,
                s.end_time AS schedule_end_time,
                'RequestForm' AS event_name,
                r.status AS approval_status,
                r.role AS roles,
                r.req_id AS id,
                r.req_category AS type,
                r.req_address, 
                r.req_person, 
                r.req_pnumber, 
                r.cal_date, 
                r.req_chapel, 
                r.req_name_pamisahan

            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
                JOIN req_form r ON s.schedule_id = r.schedule_id
                JOIN appointment_schedule a ON r.req_id = a.request_id
             
             
            WHERE 
                r.status IN ( 'Approved') AND c.citizend_id = ?  AND (a.status = 'Completed' OR a.p_status = 'Paid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function cfetchBaptismFills($regId) {
        $query = "
            SELECT 
           
            a.payable_amount,
            a.status,
           a.appsched_id AS appointment_id,
           a.reference_number,
            sch.date AS seminar_date,
            sch.start_time AS seminar_starttime,
            sch.end_time AS seminar_endtime,
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
                b.address
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                baptismfill b ON s.schedule_id = b.schedule_id
                JOIN appointment_schedule a ON b.baptism_id = a.baptismfill_id
            
                JOIN schedule sch ON a.schedule_id = sch.schedule_id 
             
            WHERE 
                b.status IN ( 'Approved') AND c.citizend_id = ?  AND (a.status = 'Completed' OR a.p_status = 'Paid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function cfetchMassBaptismFills($regId) {
        $query = "
        SELECT 
        a.payable_amount,
        a.status,
       a.appsched_id AS appointment_id,
       a.reference_number,
        se.date AS seminar_date,
        se.start_time AS seminar_starttime,
        se.end_time AS seminar_endtime,
            b.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_start_time,
            s.end_time AS schedule_end_time,
            b.event_name AS event_name,
            b.status AS approval_status,
            b.role AS roles,
            b.baptism_id AS id,
            'MassBaptism' AS type,
            b.father_fullname,
            b.pbirth,
            b.mother_fullname,
            b.religion,
            b.parent_resident,
            b.godparent,
            b.gender,
            b.c_date_birth,
            b.age,
            b.address
            FROM 
                baptismfill b
                
            JOIN 
                announcement an ON b.announcement_id = an.announcement_id
            LEFT JOIN
            citizen c ON b.citizen_id = c.citizend_id
            JOIN 
                appointment_schedule a ON b.baptism_id = a.baptismfill_id
           JOIN 
                schedule se ON an.seminar_id = se.schedule_id
          LEFT JOIN 
                schedule s ON an.schedule_id = s.schedule_id
           
             
            WHERE 
            b.status = 'Approved' AND c.citizend_id = ?  AND (a.status = 'Completed' OR a.p_status = 'Paid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function cfetchMassConfirmationFills($regId) {
        $query = "
        SELECT 
        a.payable_amount,
        a.status,
        a.appsched_id AS appointment_id,
        a.reference_number,
        se.date AS seminar_date,
            se.start_time AS seminar_starttime,
            se.end_time AS seminar_endtime,
            cf.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_start_time,
            s.end_time AS schedule_end_time,
            cf.event_name AS event_name,
            cf.status AS approval_status,
            cf.role AS roles,
            cf.confirmationfill_id AS id,
            'MassConfirmation' AS type,
            cf.fullname,
            cf.father_fullname,
            cf.date_of_baptism,
            cf.mother_fullname,
            cf.permission_to_confirm,
            cf.church_address,
            cf.name_of_church,
            cf.c_gender,
            cf.c_date_birth,
            cf.c_address
            FROM 
          confirmationfill cf
            
        JOIN 
            announcement an ON cf.announcement_id = an.announcement_id
        LEFT JOIN
        citizen c ON cf.citizen_id = c.citizend_id
        JOIN 
            appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
       JOIN 
            schedule se ON an.seminar_id = se.schedule_id
      LEFT JOIN 
            schedule s ON an.schedule_id = s.schedule_id
       
         
        WHERE 
        cf.status = 'Approved' AND c.citizend_id = ?  AND (a.status = 'Completed' OR a.p_status = 'Paid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function cfetchConfirmationFills($regId) {
        $query = "
            SELECT 
            a.payable_amount,
            a.status,
            a.appsched_id AS appointment_id,
            a.reference_number,
         
                cf.fullname AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_start_time,
                s.end_time AS schedule_end_time,
                cf.event_name AS event_name,
                cf.status AS approval_status,
                cf.role AS roles,
                cf.confirmationfill_id AS id,
                'Confirmation' AS type,
                cf.fullname,
                cf.father_fullname,
                cf.date_of_baptism,
                cf.mother_fullname,
                cf.permission_to_confirm,
                cf.church_address,
                cf.name_of_church,
                cf.c_gender,
                cf.c_date_birth,
                cf.c_address
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                confirmationfill cf ON s.schedule_id = cf.schedule_id
                JOIN appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
              
              
            WHERE 
                cf.status IN ('Approved') AND c.citizend_id = ?  AND (a.status = 'Completed' OR a.p_status = 'Paid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function cfetchMarriageFills($regId) {
        $query = "
            SELECT 
            a.payable_amount,
            a.status,
            a.appsched_id AS appointment_id,
            a.reference_number,
            sch.date AS seminar_date,
            sch.start_time AS seminar_starttime,
            sch.end_time AS seminar_endtime,
                mf.groom_name AS citizen_name,
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
                mf.bride_previously_married
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                marriagefill mf ON s.schedule_id = mf.schedule_id
                JOIN 
                appointment_schedule a ON mf.marriagefill_id = a.marriage_id
                JOIN schedule sch ON a.schedule_id = sch.schedule_id
               
            WHERE 
                mf.status IN ( 'Approved') AND c.citizend_id = ?  AND (a.status = 'Completed' OR a.p_status = 'Paid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function cfetchMassMarriageFills($regId) {
        $query = "
        SELECT 
        a.payable_amount,
        a.status,
        a.appsched_id AS appointment_id,
        a.reference_number,
        se.date AS seminar_date,
        se.start_time AS seminar_starttime,
        se.end_time AS seminar_endtime,
            mf.groom_name AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_start_time,
            s.end_time AS schedule_end_time,
            mf.event_name AS event_name,
            mf.status AS approval_status,
            mf.role AS roles,
            mf.marriagefill_id AS id,
            'MassMarriage' AS type,
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
            mf.bride_previously_married
            FROM 
         marriagefill mf
            
        JOIN 
            announcement an ON mf.announcement_id = an.announcement_id
        LEFT JOIN
        citizen c ON mf.citizen_id = c.citizend_id
        JOIN 
            appointment_schedule a ON mf.marriagefill_id = a.marriage_id
       JOIN 
            schedule se ON an.seminar_id = se.schedule_id
      LEFT JOIN 
            schedule s ON an.schedule_id = s.schedule_id
       
         
        WHERE 
        mf.status = 'Approved' AND c.citizend_id = ?  AND (a.status = 'Completed' OR a.p_status = 'Paid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function cfetchDefuctomFills($regId) {
        $query = "
            SELECT 
          a.payable_amount,
          a.status,
          a.reference_number,
            a.appsched_id AS appointment_id,
          
                df.d_fullname AS citizen_name,
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
                df.parents_residence
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                defuctomfill df ON s.schedule_id = df.schedule_id
                JOIN 
                appointment_schedule a ON df.defuctomfill_id = a.defuctom_id
              
               
            WHERE 
                df.status IN ( 'Approved') AND c.citizend_id = ? AND (a.status = 'Completed' OR a.p_status = 'Paid') ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function cgetPendingCitizens($eventType = null, $regId) {
        switch ($eventType) {
            case 'Baptism':
                return $this->cfetchBaptismFills($regId);
                case 'MassBaptism':
                return $this->cfetchMassBaptismFills($regId);
                case 'req_category':
                    return $this->cfetchRequestFills($regId);
            case 'Confirmation':
                return $this->cfetchConfirmationFills($regId);
                case 'MassConfirmation':
                return $this->cfetchMassConfirmationFills($regId);
            case 'Marriage':
                return $this->cfetchMarriageFills($regId);
                case 'MassMarriage':
                return $this->cfetchMassMarriageFills($regId);
            case 'Defuctom':
                return $this->cfetchDefuctomFills($regId);
            default:
                return array_merge(
                    $this->cfetchBaptismFills($regId),
                    $this->cfetchMassBaptismFills($regId),
                    $this->cfetchRequestFills($regId),
                    $this->cfetchConfirmationFills($regId),
                   $this->cfetchMassConfirmationFills($regId),
                    $this->cfetchMarriageFills($regId),
                    $this->cfetchMassMarriageFills($regId),
                    $this->cfetchDefuctomFills($regId)
                );
        }
    }
    public function updateBaptismStatus($baptismId, $priestId) {
        // Start a transaction
        $this->conn->begin_transaction();
        
        try {
            // Step 1: Check if an approval already exists for the given baptismId
            $sqlCheck = "SELECT approval_id FROM priest_approval WHERE approval_id = (SELECT approval_id FROM baptismfill WHERE baptism_id = ?)";
            
            // Prepare the check statement
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bind_param("i", $baptismId);
            $stmtCheck->execute();
            $stmtCheck->store_result();
    
            // Check if a result exists
            if ($stmtCheck->num_rows > 0) {
                // An approval exists, update it
                $sqlUpdateApproval = "UPDATE priest_approval 
                                       SET priest_id = ?, pr_status = 'Pending', assigned_time = NOW() 
                                       WHERE approval_id = (SELECT approval_id FROM baptismfill WHERE baptism_id = ?)";
                
                // Prepare the update statement
                $stmtUpdateApproval = $this->conn->prepare($sqlUpdateApproval);
                $stmtUpdateApproval->bind_param("ii", $priestId, $baptismId);
                
                // Execute the update statement
                if (!$stmtUpdateApproval->execute()) {
                    throw new Exception("Update priest_approval failed: " . $stmtUpdateApproval->error);
                }
                
                // Close the update statement
                $stmtUpdateApproval->close();
            } else {
                // No existing approval, insert a new record
                $sqlInsert = "INSERT INTO priest_approval (priest_id, pr_status, assigned_time) 
                              VALUES (?, 'Pending', NOW())";
    
                // Prepare the insert statement
                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->bind_param("i", $priestId);
                
                // Execute the insert statement
                if (!$stmtInsert->execute()) {
                    throw new Exception("Insert into priest_approval failed: " . $stmtInsert->error);
                }
                
                // Get the inserted approval_id
                $approvalId = $stmtInsert->insert_id;
                // Close the insert statement
                $stmtInsert->close();
                
                // Step 2: Update baptismfill to link the approval_id
                $sqlUpdate = "UPDATE baptismfill 
                              SET approval_id = ? 
                              WHERE baptism_id = ?";
                
                // Prepare the update statement
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("ii", $approvalId, $baptismId);
                
                // Execute the update statement
                if (!$stmtUpdate->execute()) {
                    throw new Exception("Update baptismfill failed: " . $stmtUpdate->error);
                }
                
                // Close the update statement
                $stmtUpdate->close();
            }
    
            // Commit the transaction
            $this->conn->commit();
            return true; // Success
            
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $this->conn->rollback();
            
            // Log the error
            error_log($e->getMessage());
            
            return false; // Failure
        }
    }
    
    
    public function updateMarriageStatus($marriageId, $priestId) {
        // Start a transaction to ensure data consistency
        $this->conn->begin_transaction();
        
        try {
            // Step 1: Check if an approval already exists for the given marriageId
            $sqlCheck = "SELECT approval_id FROM priest_approval WHERE approval_id = (SELECT approval_id FROM marriagefill WHERE marriagefill_id = ?)";
            
            // Prepare the check statement
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bind_param("i", $marriageId);
            $stmtCheck->execute();
            $stmtCheck->store_result();
    
            // Check if a result exists
            if ($stmtCheck->num_rows > 0) {
                // An approval exists, update it
                $sqlUpdateApproval = "UPDATE priest_approval 
                                       SET priest_id = ?, pr_status = 'Pending', assigned_time = NOW() 
                                       WHERE approval_id = (SELECT approval_id FROM marriagefill WHERE marriagefill_id = ?)";
                
                // Prepare the update statement
                $stmtUpdateApproval = $this->conn->prepare($sqlUpdateApproval);
                $stmtUpdateApproval->bind_param("ii", $priestId, $marriageId);
                
                // Execute the update statement
                if (!$stmtUpdateApproval->execute()) {
                    throw new Exception("Update priest_approval failed: " . $stmtUpdateApproval->error);
                }
                
                // Close the update statement
                $stmtUpdateApproval->close();
            } else {
                // No existing approval, insert a new record
                $sqlInsert = "INSERT INTO priest_approval (priest_id, pr_status, assigned_time) 
                              VALUES (?, 'Pending', NOW())";
    
                // Prepare the insert statement
                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->bind_param("i", $priestId);
                
                // Execute the insert statement
                if (!$stmtInsert->execute()) {
                    throw new Exception("Insert into priest_approval failed: " . $stmtInsert->error);
                }
                
                // Get the inserted approval_id
                $approvalId = $stmtInsert->insert_id;
                // Close the insert statement
                $stmtInsert->close();
                
                // Step 2: Update marriagefill to link the approval_id
                $sqlUpdate = "UPDATE marriagefill 
                              SET approval_id = ? 
                              WHERE marriagefill_id = ?";
                
                // Prepare the update statement
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("ii", $approvalId, $marriageId);
                
                // Execute the update statement
                if (!$stmtUpdate->execute()) {
                    throw new Exception("Update marriagefill failed: " . $stmtUpdate->error);
                }
                
                // Close the update statement
                $stmtUpdate->close();
            }
    
            // Commit the transaction
            $this->conn->commit();
            return true; // Success
            
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $this->conn->rollback();
            
            // Log the error
            error_log($e->getMessage());
            
            return false; // Failure
        }
    }
    public function updatdefuctomStatus($fpriest_id, $priestId) {
        // Start a transaction to ensure data consistency
        $this->conn->begin_transaction();
        
        try {
            // Step 1: Check if an approval already exists for the given marriageId
            $sqlCheck = "SELECT approval_id FROM priest_approval WHERE approval_id = (SELECT approval_id FROM defuctomfill WHERE defuctomfill_id = ?)";
            
            // Prepare the check statement
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bind_param("i", $fpriest_id);
            $stmtCheck->execute();
            $stmtCheck->store_result();
    
            // Check if a result exists
            if ($stmtCheck->num_rows > 0) {
                // An approval exists, update it
                $sqlUpdateApproval = "UPDATE priest_approval 
                                       SET priest_id = ?, pr_status = 'Pending', assigned_time = NOW() 
                                       WHERE approval_id = (SELECT approval_id FROM defuctomfill WHERE defuctomfill_id = ?)";
                
                // Prepare the update statement
                $stmtUpdateApproval = $this->conn->prepare($sqlUpdateApproval);
                $stmtUpdateApproval->bind_param("ii", $priestId,$fpriest_id);
                
                // Execute the update statement
                if (!$stmtUpdateApproval->execute()) {
                    throw new Exception("Update priest_approval failed: " . $stmtUpdateApproval->error);
                }
                
                // Close the update statement
                $stmtUpdateApproval->close();
            } else {
                // No existing approval, insert a new record
                $sqlInsert = "INSERT INTO priest_approval (priest_id, pr_status, assigned_time) 
                              VALUES (?, 'Pending', NOW())";
    
                // Prepare the insert statement
                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->bind_param("i", $priestId);
                
                // Execute the insert statement
                if (!$stmtInsert->execute()) {
                    throw new Exception("Insert into priest_approval failed: " . $stmtInsert->error);
                }
                
                // Get the inserted approval_id
                $approvalId = $stmtInsert->insert_id;
                // Close the insert statement
                $stmtInsert->close();
                
                // Step 2: Update marriagefill to link the approval_id
                $sqlUpdate = "UPDATE defuctomfill
                              SET approval_id = ? 
                              WHERE defuctomfill_id = ?";
                
                // Prepare the update statement
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("ii", $approvalId, $fpriest_id);
                
                // Execute the update statement
                if (!$stmtUpdate->execute()) {
                    throw new Exception("Update marriagefill failed: " . $stmtUpdate->error);
                }
                
                // Close the update statement
                $stmtUpdate->close();
            }
    
            // Commit the transaction
            $this->conn->commit();
            return true; // Success
            
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $this->conn->rollback();
            
            // Log the error
            error_log($e->getMessage());
            
            return false; // Failure
        }
    }
    
   
    
    public function updateRequestFormStatus($requestFormId, $priestId) {
        // Start a transaction to ensure data consistency
        $this->conn->begin_transaction();
        
        try {
            // Step 1: Check if an approval already exists for the given requestFormId
            $sqlCheck = "SELECT approval_id FROM priest_approval WHERE approval_id = (SELECT approval_id FROM req_form WHERE req_id = ?)";
            
            // Prepare the check statement
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bind_param("i", $requestFormId);
            $stmtCheck->execute();
            $stmtCheck->store_result();
    
            // Check if a result exists
            if ($stmtCheck->num_rows > 0) {
                // An approval exists, update it
                $sqlUpdateApproval = "UPDATE priest_approval 
                                       SET priest_id = ?, pr_status = 'Pending', assigned_time = NOW() 
                                       WHERE approval_id = (SELECT approval_id FROM req_form WHERE req_id = ?)";
                
                // Prepare the update statement
                $stmtUpdateApproval = $this->conn->prepare($sqlUpdateApproval);
                $stmtUpdateApproval->bind_param("ii", $priestId, $requestFormId);
                
                // Execute the update statement
                if (!$stmtUpdateApproval->execute()) {
                    throw new Exception("Update priest_approval failed: " . $stmtUpdateApproval->error);
                }
                
                // Close the update statement
                $stmtUpdateApproval->close();
            } else {
                // No existing approval, insert a new record
                $sqlInsert = "INSERT INTO priest_approval (priest_id, pr_status, assigned_time) 
                              VALUES (?, 'Pending', NOW())";
    
                // Prepare the insert statement
                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->bind_param("i", $priestId);
                
                // Execute the insert statement
                if (!$stmtInsert->execute()) {
                    throw new Exception("Insert into priest_approval failed: " . $stmtInsert->error);
                }
                
                // Get the inserted approval_id
                $approvalId = $stmtInsert->insert_id;
                // Close the insert statement
                $stmtInsert->close();
                
                // Step 2: Update req_form to link the approval_id
                $sqlUpdate = "UPDATE req_form 
                              SET approval_id = ? 
                              WHERE req_id = ?";
                
                // Prepare the update statement
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("ii", $approvalId, $requestFormId);
                
                // Execute the update statement
                if (!$stmtUpdate->execute()) {
                    throw new Exception("Update req_form failed: " . $stmtUpdate->error);
                }
                
                // Close the update statement
                $stmtUpdate->close();
            }
    
            // Commit the transaction
            $this->conn->commit();
            return true; // Success
            
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $this->conn->rollback();
            
            // Log the error
            error_log($e->getMessage());
            
            return false; // Failure
        }
    }
    
    
    public function updateConfirmationStatus($confirmationId, $priestId) {
        // Start a transaction to ensure data consistency
        $this->conn->begin_transaction();
        
        try {
            // Step 1: Check if an approval already exists for the given confirmationId
            $sqlCheck = "SELECT approval_id FROM priest_approval WHERE approval_id = (SELECT approval_id FROM confirmationfill WHERE confirmationfill_id = ?)";
            
            // Prepare the check statement
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bind_param("i", $confirmationId);
            $stmtCheck->execute();
            $stmtCheck->store_result();
    
            // Check if a result exists
            if ($stmtCheck->num_rows > 0) {
                // An approval exists, update it
                $sqlUpdateApproval = "UPDATE priest_approval 
                                       SET priest_id = ?, pr_status = 'Pending', assigned_time = NOW() 
                                       WHERE approval_id = (SELECT approval_id FROM confirmationfill WHERE confirmationfill_id = ?)";
                
                // Prepare the update statement
                $stmtUpdateApproval = $this->conn->prepare($sqlUpdateApproval);
                $stmtUpdateApproval->bind_param("ii", $priestId, $confirmationId);
                
                // Execute the update statement
                if (!$stmtUpdateApproval->execute()) {
                    throw new Exception("Update priest_approval failed: " . $stmtUpdateApproval->error);
                }
                
                // Close the update statement
                $stmtUpdateApproval->close();
            } else {
                // No existing approval, insert a new record
                $sqlInsert = "INSERT INTO priest_approval (priest_id, pr_status, assigned_time) 
                              VALUES (?, 'Pending', NOW())";
    
                // Prepare the insert statement
                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->bind_param("i", $priestId);
                
                // Execute the insert statement
                if (!$stmtInsert->execute()) {
                    throw new Exception("Insert into priest_approval failed: " . $stmtInsert->error);
                }
                
                // Get the inserted approval_id
                $approvalId = $stmtInsert->insert_id;
                // Close the insert statement
                $stmtInsert->close();
                
                // Step 2: Update confirmationfill to link the approval_id
                $sqlUpdate = "UPDATE confirmationfill 
                              SET approval_id = ? 
                              WHERE confirmationfill_id = ?";
                
                // Prepare the update statement
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("ii", $approvalId, $confirmationId);
                
                // Execute the update statement
                if (!$stmtUpdate->execute()) {
                    throw new Exception("Update confirmationfill failed: " . $stmtUpdate->error);
                }
                
                // Close the update statement
                $stmtUpdate->close();
            }
    
            // Commit the transaction
            $this->conn->commit();
            return true; // Success
            
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $this->conn->rollback();
            
            // Log the error
            error_log($e->getMessage());
            
            return false; // Failure
        }
    }
    
    
    
    public function insertWalkinWeddingFill($scheduleId, $priestId, $groom_name, $groom_dob, $groom_place_of_birth, $groom_citizenship, $groom_address, $groom_religion, $groom_previously_married, $groomage, $bride_name, $bride_dob, $bride_place_of_birth, $bride_citizenship, $bride_address, $bride_religion, $bride_previously_married, $brideage) {
        // First, insert into priest_approval and get the approval_id
        $approvalSql = "INSERT INTO priest_approval (priest_id, pr_status) VALUES (?, 'Pending')";
        $approvalStmt = $this->conn->prepare($approvalSql);
        $approvalStmt->bind_param("i", $priestId);
    
        if ($approvalStmt->execute()) {
            // Get the last inserted approval_id
            $approvalId = $this->conn->insert_id;
            $approvalStmt->close();
    
            // Now, insert into marriagefill with the retrieved approval_id
            $sql = "INSERT INTO marriagefill (schedule_id, approval_id, groom_name, groom_dob, groom_place_of_birth, groom_citizenship, groom_address, groom_religion, groom_previously_married, groom_age, bride_name, bride_dob, bride_place_of_birth, bride_citizenship, bride_address, bride_religion, bride_previously_married, bride_age, status, event_name, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Wedding', 'Walkin')";
            
            $stmt = $this->conn->prepare($sql);
            
            // Bind parameters
            $stmt->bind_param("iisssssssiissssssi", $scheduleId, $approvalId, $groom_name, $groom_dob, $groom_place_of_birth, $groom_citizenship, $groom_address, $groom_religion, $groom_previously_married, $groomage, $bride_name, $bride_dob, $bride_place_of_birth, $bride_citizenship, $bride_address, $bride_religion, $bride_previously_married, $brideage);
            
            if ($stmt->execute()) {
                // Return the last inserted ID
                $weddingfill_id = $this->conn->insert_id;
                $stmt->close();
                return $weddingfill_id; // Return the weddingfill ID on success
            } else {
                error_log("Insert into marriagefill failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
        } else {
            error_log("Priest approval insert failed: " . $approvalStmt->error);
            $approvalStmt->close();
            return false;
        }
    }
    
    
    
    public function insertwAppointment($weddingffill_id , $payableAmount,$eventspeaker, $scheduleId) {
        $referenceNumber = $this->generateReferenceNumber();
       $sql = "INSERT INTO appointment_schedule (marriage_id, payable_amount,speaker_app,schedule_id, status, p_status,reference_number)
                VALUES (?, ?, ?,?, 'Process', 'Unpaid',?)";
        $stmt = $this->conn->prepare($sql);
    
        // Bind parameters: 'i' for integer (baptismfill_id, priest_id), 'd' for decimal/float (payable_amount)
        $stmt->bind_param("idsis",$weddingffill_id ,$payableAmount,$eventspeaker,$scheduleId,$referenceNumber);
        if ($stmt->execute()) {
            // Get the last inserted ID
            $weddingffill_id = $this->conn->insert_id;
            $stmt->close();
            return $weddingffill_id;  // Return the ID of the newly inserted record
        } else {
            error_log("Insertion failed: " . $stmt->error);
            $stmt->close();
            return false;  // Insertion failed
        } 
    }
    public function insertrequestAppointment($requestinside, $payableAmount,) {
        $referenceNumber = $this->generateReferenceNumber();
        // Make sure to add a comma after `priest_id`
        $sql = "INSERT INTO appointment_schedule (request_id, payable_amount, status, p_status, reference_number)
                VALUES (?, ?,  'Process', 'Unpaid', ?)";
    
        $stmt = $this->conn->prepare($sql);
    
        // Bind parameters: 'i' for integer (req_id, priest_id), 'd' for decimal/float (payable_amount)
        $stmt->bind_param("ids", $requestinside, $payableAmount,  $referenceNumber);
    
        if ($stmt->execute()) {
            // Get the last inserted ID
            $appointment_id = $this->conn->insert_id; // Adjusted variable name
            $stmt->close();
            return $appointment_id; // Return the ID of the newly inserted record
        } else {
            error_log("Insertion failed: " . $stmt->error);
            $stmt->close();
            return false; // Insertion failed
        }
    }
    
    public function insertAppointment($baptismfillId = null, $payableAmount = null,$eventspeaker = null,  $scheduleId = null) {
        // Generate a random 12-letter reference number
        $referenceNumber = $this->generateReferenceNumber();
    
        $sql = "INSERT INTO appointment_schedule (baptismfill_id, payable_amount,speaker_app, schedule_id, status, p_status, reference_number) 
                VALUES (?,  ?, ?,?, 'Process','Unpaid', ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("idsis", $baptismfillId, $payableAmount,$eventspeaker, $scheduleId, $referenceNumber);
    
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
    public function insertcAppointment($confirmationId= null, $payableAmount = null,) {
        // Generate a random 12-letter reference number
        $referenceNumber = $this->generateReferenceNumber();
    
        $sql = "INSERT INTO appointment_schedule (confirmation_id, payable_amount,  status, p_status, reference_number) 
                VALUES (?,  ?,  'Process','Unpaid', ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ids", $confirmationId, $payableAmount,  $referenceNumber);
    
        if ($stmt->execute()) {
            // Get the last inserted ID
            $confirmationId = $this->conn->insert_id;
            $stmt->close();
            return $confirmationId;  // Return the ID of the newly inserted record
        } else {
            error_log("Insertion failed: " . $stmt->error);
            $stmt->close();
            return false;  // Insertion failed
        }
    }
    public function insertWalkinFuneralFill($scheduleId, $priestId, $d_fullname, $d_address, $d_gender, $cause_of_death, $marital_status, $place_of_birth, $father_fullname, $date_of_birth, $birthage, $mother_fullname, $parents_residence, $date_of_death, $place_of_death) {
        // First, insert into priest_approval and get the approval_id
        $approvalSql = "INSERT INTO priest_approval (priest_id, pr_status) VALUES (?, 'Pending')";
        $approvalStmt = $this->conn->prepare($approvalSql);
        $approvalStmt->bind_param("i", $priestId);
    
        if ($approvalStmt->execute()) {
            // Get the last inserted approval_id
            $approvalId = $this->conn->insert_id;
            $approvalStmt->close();
    
            // Now, insert into defuctomfill with the retrieved approval_id
            $sql = "INSERT INTO defuctomfill (schedule_id, approval_id, d_fullname, d_address, d_gender, cause_of_death, marital_status, place_of_birth, father_fullname, date_of_birth, age, mother_fullname, parents_residence, date_of_death, place_of_death, status, event_name, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Funeral', 'Walkin')";
            
            $stmt = $this->conn->prepare($sql);
            
            // Bind parameters
            $stmt->bind_param("iissssssssissss", $scheduleId, $approvalId, $d_fullname, $d_address, $d_gender, $cause_of_death, $marital_status, $place_of_birth, $father_fullname, $date_of_birth, $birthage, $mother_fullname, $parents_residence, $date_of_death, $place_of_death);
            
            if ($stmt->execute()) {
                // Return the last inserted ID
                $defuctomfill_id = $this->conn->insert_id;
                $stmt->close();
                return $defuctomfill_id; // Return the defuctomfill ID on success
            } else {
                error_log("Insert into defuctomfill failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
        } else {
            error_log("Priest approval insert failed: " . $approvalStmt->error);
            $approvalStmt->close();
            return false;
        }
    }
    
    public function insertfAppointment( $defuctomfill_id, $payableAmount) {
        $referenceNumber = $this->generateReferenceNumber();
       $sql = "INSERT INTO appointment_schedule (defuctom_id, payable_amount,  status, p_status,reference_number)
                VALUES (?, ?, 'Process', 'Unpaid',?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ids", $defuctomfill_id ,$payableAmount, $referenceNumber);
    
        if ($stmt->execute()) {
            // Get the last inserted ID
            $defuctomfill_id = $this->conn->insert_id;
            $stmt->close();
            return $defuctomfill_id;  // Return the ID of the newly inserted record
        } else {
            error_log("Insertion failed: " . $stmt->error);
            $stmt->close();
            return false;  // Insertion failed
        }
    }
    public function insertIntoWalkinConfirmFill($scheduleId, $priestId, $fullname, $gender, $c_date_birth, $address, $date_of_baptism, $name_of_church, $father_fullname, $mother_fullname, $permission_to_confirm, $church_address, $age) {
        // First, insert into priest_approval and get the approval_id
        $approvalSql = "INSERT INTO priest_approval (priest_id, pr_status) VALUES (?, 'Pending')";
        $approvalStmt = $this->conn->prepare($approvalSql);
        $approvalStmt->bind_param("i", $priestId);
    
        if ($approvalStmt->execute()) {
            // Get the last inserted approval_id
            $approvalId = $this->conn->insert_id;
            $approvalStmt->close();
    
            // Now, insert into confirmationfill with the retrieved approval_id
            $sql = "INSERT INTO confirmationfill (schedule_id, approval_id, fullname, c_gender, c_date_birth, c_address, date_of_baptism, name_of_church, father_fullname, mother_fullname, permission_to_confirm, church_address, c_age, status, event_name, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Confirmation', 'Walkin')";
            
            $stmt = $this->conn->prepare($sql);
            
            // Bind parameters
            $stmt->bind_param("iissssssssssi", $scheduleId, $approvalId, $fullname, $gender, $c_date_birth, $address, $date_of_baptism, $name_of_church, $father_fullname, $mother_fullname, $permission_to_confirm, $church_address, $age);
        
            if ($stmt->execute()) {
                // Return the last inserted ID
                $confirmationId = $this->conn->insert_id;
                $stmt->close();
                return $confirmationId; // Return the confirmationId on success
            } else {
                error_log("Confirmation fill insert failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
        } else {
            error_log("Priest approval insert failed: " . $approvalStmt->error);
            $approvalStmt->close();
            return false;
        }
    }
    
    public function insertIntoWalkinBaptismFill($scheduleId,$priestId, $fatherFullname, $fullname, $gender, $c_date_birth, $address, $pbirth, $mother_fullname, $religion, $parentResident, $godparent, $age)  {
        // First, insert into priest_approval and get the approval_id
        $approvalSql = "INSERT INTO priest_approval (priest_id, pr_status) VALUES (?, 'Pending')";
        $approvalStmt = $this->conn->prepare($approvalSql);
        $approvalStmt->bind_param("i", $priestId);
    
        if ($approvalStmt->execute()) {
            // Get the last inserted approval_id
            $approvalId = $this->conn->insert_id;
            $approvalStmt->close();
    
            // Now, insert into baptismfill with the retrieved approval_id
            $sql = "INSERT INTO baptismfill (schedule_id, approval_id, father_fullname, fullname, gender, c_date_birth, address, pbirth, mother_fullname, religion, parent_resident, godparent, age, status, event_name, role, created_at) 
                    VALUES ( ?,?,? ,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Baptism', 'Walk In', CURRENT_TIMESTAMP)";
            
            $stmt = $this->conn->prepare($sql);
            
            // Bind parameters
            $stmt->bind_param("iissssssssssi", $scheduleId, $approvalId, $fatherFullname, $fullname, $gender, $c_date_birth, $address, $pbirth, $mother_fullname, $religion, $parentResident, $godparent, $age);
        
            if ($stmt->execute()) {
                // Get the last inserted baptismfill ID
                $baptismfillId = $this->conn->insert_id;
                $stmt->close();
                return $baptismfillId; // Return the baptismfillId on success
            } else {
                error_log("Baptism fill insert failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
        } else {
            error_log("Priest approval insert failed: " . $approvalStmt->error);
            $approvalStmt->close();
            return false;
        }
    }
    
    public function fetchRequestFillss($regId) {
        $query = "
            SELECT 
                
                r.req_person AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_start_time,
                s.end_time AS schedule_end_time,
               r.req_category AS event_name,
                r.status AS approval_status,
                r.role AS roles,
                r.req_id AS id,
                r.req_category AS type
             
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
               req_form r ON s.schedule_id = r.schedule_id
             
            WHERE 
                r.status IN ( 'Pending') AND c.citizend_id = ? ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function fetchBaptismFillss($regId) {
        $query = "
            SELECT 
                
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
                b.address
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                baptismfill b ON s.schedule_id = b.schedule_id
             
            WHERE 
                b.status IN ( 'Pending') AND c.citizend_id = ? ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function fetchConfirmationFillss($regId) {
        $query = "
            SELECT 
   
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
                cf.c_address
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                confirmationfill cf ON s.schedule_id = cf.schedule_id
             
              
            WHERE 
                cf.status IN ('Pending') AND c.citizend_id = ? ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function fetchMarriageFillss($regId) {
        $query = "
            SELECT 
   
                mf.groom_name AS citizen_name,
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
                mf.bride_previously_married
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                marriagefill mf ON s.schedule_id = mf.schedule_id
           
       
            WHERE 
                mf.status IN ( 'Pending') AND c.citizend_id = ? ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function fetchDefuctomFillss($regId) {
        $query = "
            SELECT 

                df.d_fullname AS citizen_name,
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
                df.parents_residence
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                defuctomfill df ON s.schedule_id = df.schedule_id
              
              
            WHERE 
                df.status IN ( 'Pending') AND c.citizend_id = ? ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getPendingCitizenss($eventType = null, $regId) {
        switch ($eventType) {
            case 'Baptism':
                return $this->fetchBaptismFillss($regId);
                case 'req_category':
                return $this->fetchRequestFillss($regId);
            case 'Confirmation':
                return $this->fetchConfirmationFillss($regId);
            case 'Marriage':
                return $this->fetchMarriageFillss($regId);
            case 'Defuctom':
                return $this->fetchDefuctomFillss($regId);
            default:
                return array_merge(
                    $this->fetchBaptismFillss($regId),
                    $this->fetchRequestFillss($regId),
                    $this->fetchConfirmationFillss($regId),
                    $this->fetchMarriageFillss($regId),
                    $this->fetchDefuctomFillss($regId)
                );
        }
    }
   
    public function fetchRequestFills($regId) {
        $query = "
            SELECT 
           
            a.payable_amount,
            a.status,
           a.appsched_id AS appointment_id,
           a.reference_number,
            r.req_person AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_start_time,
                s.end_time AS schedule_end_time,
                'RequestForm' AS event_name,
                r.status AS approval_status,
                r.role AS roles,
                r.req_id AS id,
                r.req_category AS type,
                r.req_address, 
                r.req_person, 
                r.req_pnumber, 
                r.cal_date, 
                r.req_chapel, 
                r.req_name_pamisahan

            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
                JOIN req_form r ON s.schedule_id = r.schedule_id
                JOIN appointment_schedule a ON r.req_id = a.request_id
             
             
            WHERE 
                r.status IN ( 'Approved') AND c.citizend_id = ?  AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function fetchBaptismFills($regId) {
        $query = "
            SELECT 
           
            a.payable_amount,
            a.status,
           a.appsched_id AS appointment_id,
           a.reference_number,
            sch.date AS seminar_date,
            sch.start_time AS seminar_starttime,
            sch.end_time AS seminar_endtime,
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
                b.address
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                baptismfill b ON s.schedule_id = b.schedule_id
                JOIN appointment_schedule a ON b.baptism_id = a.baptismfill_id
            
                JOIN schedule sch ON a.schedule_id = sch.schedule_id 
             
            WHERE 
                b.status IN ( 'Approved') AND c.citizend_id = ?  AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function fetchMassBaptismFills($regId) {
        $query = "
        SELECT 
        a.payable_amount,
        a.status,
       a.appsched_id AS appointment_id,
       a.reference_number,
        se.date AS seminar_date,
        se.start_time AS seminar_starttime,
        se.end_time AS seminar_endtime,
            b.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_start_time,
            s.end_time AS schedule_end_time,
            b.event_name AS event_name,
            b.status AS approval_status,
            b.role AS roles,
            b.baptism_id AS id,
            'MassBaptism' AS type,
            b.father_fullname,
            b.pbirth,
            b.mother_fullname,
            b.religion,
            b.parent_resident,
            b.godparent,
            b.gender,
            b.c_date_birth,
            b.age,
            b.address
            FROM 
                baptismfill b
                
            JOIN 
                announcement an ON b.announcement_id = an.announcement_id
            LEFT JOIN
            citizen c ON b.citizen_id = c.citizend_id
            JOIN 
                appointment_schedule a ON b.baptism_id = a.baptismfill_id
           JOIN 
                schedule se ON an.seminar_id = se.schedule_id
          LEFT JOIN 
                schedule s ON an.schedule_id = s.schedule_id
           
             
            WHERE 
            b.status = 'Approved' AND c.citizend_id = ?  AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function fetchMassConfirmationFills($regId) {
        $query = "
        SELECT 
        a.payable_amount,
        a.status,
        a.appsched_id AS appointment_id,
        a.reference_number,
        se.date AS seminar_date,
            se.start_time AS seminar_starttime,
            se.end_time AS seminar_endtime,
            cf.fullname AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_start_time,
            s.end_time AS schedule_end_time,
            cf.event_name AS event_name,
            cf.status AS approval_status,
            cf.role AS roles,
            cf.confirmationfill_id AS id,
            'MassConfirmation' AS type,
            cf.fullname,
            cf.father_fullname,
            cf.date_of_baptism,
            cf.mother_fullname,
            cf.permission_to_confirm,
            cf.church_address,
            cf.name_of_church,
            cf.c_gender,
            cf.c_date_birth,
            cf.c_address
            FROM 
          confirmationfill cf
            
        JOIN 
            announcement an ON cf.announcement_id = an.announcement_id
        LEFT JOIN
        citizen c ON cf.citizen_id = c.citizend_id
        JOIN 
            appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
       JOIN 
            schedule se ON an.seminar_id = se.schedule_id
      LEFT JOIN 
            schedule s ON an.schedule_id = s.schedule_id
       
         
        WHERE 
        cf.status = 'Approved' AND c.citizend_id = ?  AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function fetchConfirmationFills($regId) {
        $query = "
            SELECT 
            a.payable_amount,
            a.status,
            a.appsched_id AS appointment_id,
            a.reference_number,
         
                cf.fullname AS citizen_name,
                s.date AS schedule_date,
                s.start_time AS schedule_start_time,
                s.end_time AS schedule_end_time,
                cf.event_name AS event_name,
                cf.status AS approval_status,
                cf.role AS roles,
                cf.confirmationfill_id AS id,
                'Confirmation' AS type,
                cf.fullname,
                cf.father_fullname,
                cf.date_of_baptism,
                cf.mother_fullname,
                cf.permission_to_confirm,
                cf.church_address,
                cf.name_of_church,
                cf.c_gender,
                cf.c_date_birth,
                cf.c_address
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                confirmationfill cf ON s.schedule_id = cf.schedule_id
                JOIN appointment_schedule a ON cf.confirmationfill_id = a.confirmation_id
              
              
            WHERE 
                cf.status IN ('Approved') AND c.citizend_id = ?  AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function fetchMarriageFills($regId) {
        $query = "
            SELECT 
            a.payable_amount,
            a.status,
            a.appsched_id AS appointment_id,
            a.reference_number,
            sch.date AS seminar_date,
            sch.start_time AS seminar_starttime,
            sch.end_time AS seminar_endtime,
                mf.groom_name AS citizen_name,
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
                mf.bride_previously_married
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                marriagefill mf ON s.schedule_id = mf.schedule_id
                JOIN 
                appointment_schedule a ON mf.marriagefill_id = a.marriage_id
                JOIN schedule sch ON a.schedule_id = sch.schedule_id
               
            WHERE 
                mf.status IN ( 'Approved') AND c.citizend_id = ?  AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function fetchMassMarriageFills($regId) {
        $query = "
        SELECT 
        a.payable_amount,
        a.status,
        a.appsched_id AS appointment_id,
        a.reference_number,
        se.date AS seminar_date,
        se.start_time AS seminar_starttime,
        se.end_time AS seminar_endtime,
            mf.groom_name AS citizen_name,
            s.date AS schedule_date,
            s.start_time AS schedule_start_time,
            s.end_time AS schedule_end_time,
            mf.event_name AS event_name,
            mf.status AS approval_status,
            mf.role AS roles,
            mf.marriagefill_id AS id,
            'MassMarriage' AS type,
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
            mf.bride_previously_married
            FROM 
         marriagefill mf
            
        JOIN 
            announcement an ON mf.announcement_id = an.announcement_id
        LEFT JOIN
        citizen c ON mf.citizen_id = c.citizend_id
        JOIN 
            appointment_schedule a ON mf.marriagefill_id = a.marriage_id
       JOIN 
            schedule se ON an.seminar_id = se.schedule_id
      LEFT JOIN 
            schedule s ON an.schedule_id = s.schedule_id
       
         
        WHERE 
        mf.status = 'Approved' AND c.citizend_id = ?  AND (a.status = 'Process' OR a.p_status = 'Unpaid')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function fetchDefuctomFills($regId) {
        $query = "
            SELECT 
          a.payable_amount,
          a.status,
          a.reference_number,
            a.appsched_id AS appointment_id,
          
                df.d_fullname AS citizen_name,
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
                df.parents_residence
            FROM 
                citizen c
            JOIN 
                schedule s ON c.citizend_id = s.citizen_id
            JOIN 
                defuctomfill df ON s.schedule_id = df.schedule_id
                JOIN 
                appointment_schedule a ON df.defuctomfill_id = a.defuctom_id
              
               
            WHERE 
                df.status IN ( 'Approved') AND c.citizend_id = ? AND (a.status = 'Process' OR a.p_status = 'Unpaid') ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $regId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function getPendingCitizens($eventType = null, $regId) {
        switch ($eventType) {
            case 'Baptism':
                return $this->fetchBaptismFills($regId);
                case 'MassBaptism':
                return $this->fetchMassBaptismFills($regId);
                case 'req_category':
                    return $this->fetchRequestFills($regId);
            case 'Confirmation':
                return $this->fetchConfirmationFills($regId);
                case 'MassConfirmation':
                return $this->fetchMassConfirmationFills($regId);
            case 'Marriage':
                return $this->fetchMarriageFills($regId);
                case 'MassMarriage':
                return $this->fetchMassMarriageFills($regId);
            case 'Defuctom':
                return $this->fetchDefuctomFills($regId);
            default:
                return array_merge(
                    $this->fetchBaptismFills($regId),
                    $this->fetchMassBaptismFills($regId),
                    $this->fetchRequestFills($regId),
                    $this->fetchConfirmationFills($regId),
                   $this->fetchMassConfirmationFills($regId),
                    $this->fetchMarriageFills($regId),
                    $this->fetchMassMarriageFills($regId),
                    $this->fetchDefuctomFills($regId)
                );
        }
    }

    

    public function insertIntoMassConfirmFill(
        $citizenId, $announcementId, $fullname, $gender, 
        $c_date_birth, $cage, $address, $date_of_baptism, 
        $name_of_church, $father_fullname, $mother_fullname, 
        $permission_to_confirm, $church_address
    ) {
        // Begin a transaction
        $this->conn->begin_transaction();
    
        try {
            // Prepare the insert statement for confirmationfill
            $sql = "INSERT INTO confirmationfill (
                citizen_id, announcement_id, fullname, c_gender, 
                c_date_birth, c_age, c_address, date_of_baptism, 
                name_of_church, father_fullname, mother_fullname, 
                permission_to_confirm, church_address, status, event_name, role
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'MassConfirmation', 'Online')";
    
            $stmt = $this->conn->prepare($sql);
            if ($stmt === FALSE) {
                throw new Exception("Error: " . $this->conn->error);
            }
    
            // Bind parameters
            $stmt->bind_param(
                "iisssisssssss",
                $citizenId, $announcementId, $fullname, $gender, 
                $c_date_birth, $cage, $address, $date_of_baptism, 
                $name_of_church, $father_fullname, $mother_fullname, 
                $permission_to_confirm, $church_address
            );
    
            // Execute the insert
            if ($stmt->execute() === FALSE) {
                throw new Exception("Error: " . $stmt->error);
            }
    
            // Update the capacity
            $updateStmt = $this->conn->prepare("
                UPDATE announcement SET capacity = capacity - 1 WHERE announcement_id = ?
            ");
    
            if ($updateStmt === FALSE) {
                throw new Exception("Error: " . $this->conn->error);
            }
    
            $updateStmt->bind_param("i", $announcementId);
    
            if ($updateStmt->execute() === FALSE) {
                throw new Exception("Error: " . $updateStmt->error);
            }
    
            // Commit the transaction
            $this->conn->commit();
    
            // Close statements
            $stmt->close();
            $updateStmt->close();
    
            return true;
    
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $this->conn->rollback();
            error_log($e->getMessage());
            return false; // Or handle the error as needed
        }
    }
    
    public function insertIntowalkinMassConfirmFill(
        $announcementId, $fullname, $gender, $c_date_birth, 
        $cage, $address, $date_of_baptism, $name_of_church, 
        $father_fullname, $mother_fullname, $permission_to_confirm, 
        $church_address
    ) {
        // Begin a transaction
        $this->conn->begin_transaction();
    
        try {
            // Prepare the insert statement for confirmationfill
            $sql = "INSERT INTO confirmationfill (
                announcement_id, fullname, c_gender, c_date_birth, 
                c_age, c_address, date_of_baptism, name_of_church, 
                father_fullname, mother_fullname, permission_to_confirm, 
                church_address, status, event_name, role
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'MassConfirmation', 'Walkin')";
    
            $stmt = $this->conn->prepare($sql);
            if ($stmt === FALSE) {
                throw new Exception("Error: " . $this->conn->error);
            }
    
            // Bind parameters
            $stmt->bind_param(
                "isssisssssss",
                $announcementId, $fullname, $gender, $c_date_birth, 
                $cage, $address, $date_of_baptism, $name_of_church, 
                $father_fullname, $mother_fullname, $permission_to_confirm, 
                $church_address
            );
    
            // Execute the insert
            if ($stmt->execute() === FALSE) {
                throw new Exception("Error: " . $stmt->error);
            }
    
            // Update the capacity
            $updateStmt = $this->conn->prepare("
                UPDATE announcement SET capacity = capacity - 1 WHERE announcement_id = ?
            ");
    
            if ($updateStmt === FALSE) {
                throw new Exception("Error: " . $this->conn->error);
            }
    
            $updateStmt->bind_param("i", $announcementId);
    
            if ($updateStmt->execute() === FALSE) {
                throw new Exception("Error: " . $updateStmt->error);
            }
    
            // Commit the transaction
            $this->conn->commit();
    
            // Close statements
            $stmt->close();
            $updateStmt->close();
    
            return true;
    
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $this->conn->rollback();
            error_log($e->getMessage());
            return false; // Or handle the error as needed
        }
    }
    
    
    public function insertMassWeddingFill($citizenId, $announcementId, $groom_name, $groom_dob, $groom_place_of_birth, $groom_citizenship, $groom_address, $groom_religion, $groom_previously_married, $groomage, $bride_name, $bride_dob, $bride_place_of_birth, $bride_citizenship, $bride_address, $bride_religion, $bride_previously_married, $brideage, $status, $event_name, $role) {
    
        // Insert into marriagefill table
        $sql = "INSERT INTO marriagefill (citizen_id, announcement_id, groom_name, groom_dob, groom_place_of_birth, groom_citizenship, groom_address, groom_religion, groom_previously_married, groom_age, bride_name, bride_dob, bride_place_of_birth, bride_citizenship, bride_address, bride_religion, bride_previously_married, bride_age, status, event_name, role) 
                VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?,?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisssssssisssssssisss", $citizenId, $announcementId, $groom_name, $groom_dob, $groom_place_of_birth, $groom_citizenship, $groom_address, $groom_religion, $groom_previously_married, $groomage, $bride_name, $bride_dob, $bride_place_of_birth, $bride_citizenship, $bride_address, $bride_religion, $bride_previously_married, $brideage, $status, $event_name, $role);
        
        if ($stmt->execute()) {
            // After successful insertion, decrement the capacity in the announcement table
            $updateCapacitySql = "UPDATE announcement SET capacity = capacity - 1 WHERE announcement_id = ?";
            $stmtUpdate = $this->conn->prepare($updateCapacitySql);
            $stmtUpdate->bind_param("i", $announcementId);
            
            if ($stmtUpdate->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    
    public function insertwalkinMassWeddingFill($announcementId, $groom_name, $groom_dob, $groom_place_of_birth, $groom_citizenship, $groom_address, $groom_religion, $groom_previously_married, $groomage, $bride_name, $bride_dob, $bride_place_of_birth, $bride_citizenship, $bride_address, $bride_religion, $bride_previously_married, $brideage, $status, $event_name, $role) {

        // Begin a transaction
        $this->conn->begin_transaction();
    
        try {
            // Insert into marriagefill table
            $sql = "INSERT INTO marriagefill (announcement_id, groom_name, groom_dob, groom_place_of_birth, groom_citizenship, groom_address, groom_religion, groom_previously_married, groom_age, bride_name, bride_dob, bride_place_of_birth, bride_citizenship, bride_address, bride_religion, bride_previously_married, bride_age, status, event_name, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("isssssssisssssssisss", $announcementId, $groom_name, $groom_dob, $groom_place_of_birth, $groom_citizenship, $groom_address, $groom_religion, $groom_previously_married, $groomage, $bride_name, $bride_dob, $bride_place_of_birth, $bride_citizenship, $bride_address, $bride_religion, $bride_previously_married, $brideage, $status, $event_name, $role);
    
            if (!$stmt->execute()) {
                throw new Exception("Error inserting marriage fill");
            }
    
            // Update the capacity in the announcement table (reduce by 1)
            $sqlUpdate = "UPDATE announcement SET capacity = capacity - 1 WHERE announcement_id = ? AND capacity > 0";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("i", $announcementId);
    
            if (!$stmtUpdate->execute()) {
                throw new Exception("Error updating capacity");
            }
    
            // Commit transaction if both queries are successful
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction in case of error
            $this->conn->rollback();
            return false;
        }
    }
    
    
    
    
    public function insertMassBaptismFill(
        $citizenId, $announcementId, $fullname, $gender, $address, 
        $dateOfBirth, $fatherFullname, $placeOfBirth, $motherFullname, 
        $religion, $parentResident, $godparent, $age, $status, 
        $eventName, $role
    ) {
        // Begin a transaction
        $this->conn->begin_transaction();
    
        try {
            // Prepare the insert statement for baptismfill
            $stmt = $this->conn->prepare("
                INSERT INTO baptismfill (
                    citizen_id, announcement_id, fullname, gender, address, 
                    c_date_birth, father_fullname, pbirth, mother_fullname, 
                    religion, parent_resident, godparent, age, status, 
                    event_name, role
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt === FALSE) {
                throw new Exception("Error: " . $this->conn->error);
            }
        
            // Bind parameters
            $stmt->bind_param(
                "iissssssssssisss",
                $citizenId, $announcementId, $fullname, $gender, $address, 
                $dateOfBirth, $fatherFullname, $placeOfBirth, 
                $motherFullname, $religion, $parentResident, 
                $godparent, $age, $status, $eventName, $role
            );
        
            // Execute the insert
            if ($stmt->execute() === FALSE) {
                throw new Exception("Error: " . $stmt->error);
            }
        
            // Update the capacity
            $updateStmt = $this->conn->prepare("
                UPDATE announcement SET capacity = capacity - 1 WHERE announcement_id = ?
            ");
            
            if ($updateStmt === FALSE) {
                throw new Exception("Error: " . $this->conn->error);
            }
    
            $updateStmt->bind_param("i", $announcementId);
    
            if ($updateStmt->execute() === FALSE) {
                throw new Exception("Error: " . $updateStmt->error);
            }
            
            // Commit the transaction
            $this->conn->commit();
    
            // Close statements
            $stmt->close();
            $updateStmt->close();
    
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $this->conn->rollback();
            die($e->getMessage());
        }
    }
    
    
    public function insertwalkinMassBaptismFill(
        $announcementId, $fullname, $gender, $address, $dateOfBirth,
        $fatherFullname, $placeOfBirth, $motherFullname, $religion,
        $parentResident, $godparent, $age, $status, $eventName, $role
    ) {
        // Begin a transaction
        $this->conn->begin_transaction();
    
        try {
            // Prepare the insert statement for baptismfill
            $stmt = $this->conn->prepare("
                INSERT INTO baptismfill (
                  announcement_id, fullname, gender, address, c_date_birth,
                  father_fullname, pbirth, mother_fullname, religion,
                  parent_resident, godparent, age, status, event_name, role
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
    
            if ($stmt === FALSE) {
                throw new Exception("Error: " . $this->conn->error);
            }
    
            // Bind parameters
            $stmt->bind_param(
                "issssssssssisss",
                $announcementId, $fullname, $gender, $address, $dateOfBirth,
                $fatherFullname, $placeOfBirth, $motherFullname, $religion,
                $parentResident, $godparent, $age, $status, $eventName, $role
            );
    
            // Execute the insert
            if ($stmt->execute() === FALSE) {
                throw new Exception("Error: " . $stmt->error);
            }
    
            // Update the capacity
            $updateStmt = $this->conn->prepare("
                UPDATE announcement SET capacity = capacity - 1 WHERE announcement_id = ?
            ");
    
            if ($updateStmt === FALSE) {
                throw new Exception("Error: " . $this->conn->error);
            }
    
            $updateStmt->bind_param("i", $announcementId);
    
            if ($updateStmt->execute() === FALSE) {
                throw new Exception("Error: " . $updateStmt->error);
            }
    
            // Commit the transaction
            $this->conn->commit();
    
            // Close statements
            $stmt->close();
            $updateStmt->close();
    
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $this->conn->rollback();
            die($e->getMessage());
        }
    }
    
    
    
    public function getFetchDetails($email) {
        $sql = "SELECT `citizend_id`, `fullname`, `gender`, `c_date_birth`,  `address`,  `phone`FROM `citizen` WHERE `email` = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return null;
        }
    
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->errno) {
            error_log("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $details = $result->fetch_assoc();
        $stmt->close();
    
        if (!$details) {
            error_log("No details found for email: " . $email);
            return null;
        }
    
        // Split fullname into components
        $names = explode(' ', $details['fullname']);
        $details['firstname'] = $names[0];
        $details['lastname'] = end($names);
        
        // Handle the case where there's a middle name
        if (count($names) > 2) {
            $details['middlename'] = implode(' ', array_slice($names, 1, -1));
        } else {
            $details['middlename'] = '';
        }
    
        return $details;
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

    public function insertIntoBaptismFill($scheduleId, $fatherFullname, $fullname, $gender, $c_date_birth, $address, $pbirth, $mother_fullname, $religion, $parentResident, $godparent,$age) {
        $sql = "INSERT INTO baptismfill (schedule_id, father_fullname, fullname, gender, c_date_birth, address, pbirth, mother_fullname, religion, parent_resident, godparent,age, status, event_name, role,created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, 'Pending', 'Baptism', 'Online',CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($sql);
    
        // Use 'issssssssss' for the type definition string, corresponding to the parameters
        $stmt->bind_param("issssssssssi", $scheduleId, $fatherFullname, $fullname, $gender, $c_date_birth, $address, $pbirth, $mother_fullname, $religion, $parentResident, $godparent,$age);

        if (!$stmt->execute()) {
            error_log("Insert failed: " . $stmt->error);
        }
        $stmt->close();
    }
    public function insertoutsideRequestFormFill($scheduleId = null, $priestId = null, $selectrequest = null, $fullname = null, $datetofollowup = null, $address = null, $cpnumber = null, $fullnames = null, $chapel = null, $role = null, $event_location = null) {
        // First, insert into priest_approval and get the approval_id
        $approvalSql = "INSERT INTO priest_approval (priest_id, pr_status) VALUES (?, 'Pending')";
        $approvalStmt = $this->conn->prepare($approvalSql);
        $approvalStmt->bind_param("i", $priestId);
        
        if ($approvalStmt->execute()) {
            // Get the last inserted approval_id
            $approvalId = $this->conn->insert_id;
            $approvalStmt->close();
    
            // Now, insert into req_form with the retrieved approval_id
            $sql = "INSERT INTO req_form (schedule_id, approval_id, req_category, req_person, cal_date, req_address, req_pnumber, req_name_pamisahan, req_chapel, status, role, event_location, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, CURRENT_TIMESTAMP)";
            
            $stmt = $this->conn->prepare($sql);
            
            // Bind parameters
            $stmt->bind_param("iisssssssss", $scheduleId, $approvalId, $selectrequest, $fullname, $datetofollowup, $address, $cpnumber, $fullnames, $chapel, $role, $event_location);
            
            if ($stmt->execute()) {
                // Return the last inserted ID
                $requestId = $this->conn->insert_id;
                $stmt->close();
                return $requestId; // Return the ID of the newly inserted record
            } else {
                error_log("Insert failed: " . $stmt->error);
                $stmt->close();
                return false; // Insertion failed
            }
        } else {
            error_log("Priest approval insert failed: " . $approvalStmt->error);
            $approvalStmt->close();
            return false;
        }
    }
    
    public function insertRequestFormFill($scheduleId = null, $selectrequest = null, $fullname = null, $datetofollowup = null, $address = null, $cpnumber = null, $fullnames = null, $chapel = null, $role = null, $event_location = null) {
        // Corrected SQL with schedule_id included
        $sql = "INSERT INTO req_form (schedule_id, req_category, req_person, cal_date, req_address, req_pnumber, req_name_pamisahan, req_chapel, status, role, event_location, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $this->conn->prepare($sql);
        
        // Updated type definition string to match 10 variables
        $stmt->bind_param("isssssssss", $scheduleId, $selectrequest, $fullname, $datetofollowup, $address, $cpnumber, $fullnames, $chapel, $role, $event_location);
        
        if ($stmt->execute()) {
            // Return the last inserted ID
            $requestId = $this->conn->insert_id;
            $stmt->close();
            return $requestId; // Return the ID of the newly inserted record
        } else {
            error_log("Insert failed: " . $stmt->error);
            $stmt->close();
            return false; // Insertion failed
        }
    }
    
    
    
    public function insertIntoConfirmFill($scheduleId, $fullname, $gender, $c_date_birth, $address,  $date_of_baptism, $name_of_church, $father_fullname, $mother_fullname, $permission_to_confirm, $church_address,$age) {
        $sql = "INSERT INTO confirmationfill (schedule_id, fullname, c_gender, c_date_birth, c_address,  date_of_baptism, name_of_church, father_fullname, mother_fullname, permission_to_confirm, church_address,c_age, status, event_name, role) 
                VALUES (?, ?, ?,  ?, ?, ?,  ?, ?, ?, ?, ?,?, 'Pending', 'Confirmation', 'Online')";
        $stmt = $this->conn->prepare($sql);
        
        // Use 'isssssssssssss' for the type definition string, corresponding to the parameters
        $stmt->bind_param("issssssssssi", $scheduleId, $fullname, $gender, $c_date_birth, $address,  $date_of_baptism, $name_of_church, $father_fullname, $mother_fullname, $permission_to_confirm, $church_address,$age);
    
        if (!$stmt->execute()) {
            error_log("Insert failed: " . $stmt->error);
        }
        $stmt->close();
    }
    
    public function insertSchedule($citizenId, $date, $startTime, $endTime) {
        $sql = "INSERT INTO `schedule` (`citizen_id`, `date`, `start_time`, `end_time`, `event_type`) VALUES (?, ?, ?, ?,'Appointment')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $citizenId, $date, $startTime, $endTime);
        if (!$stmt->execute()) {
            error_log("Insert failed: " . $stmt->error);
            return false;
        }
        // Return the ID of the inserted schedule
        return $this->conn->insert_id;
    }
    public function insertFuneralFill($scheduleId, $d_fullname, $d_address, $d_gender, $cause_of_death, $marital_status, $place_of_birth, $father_fullname, $date_of_birth,$birthage, $mother_fullname, $parents_residence, $date_of_death, $place_of_death) {
        $status = 'Pending';
        $event_name = 'Funeral';
        $role = 'Online';
    
        $sql = "INSERT INTO defuctomfill (schedule_id, d_fullname, d_address, d_gender, cause_of_death, marital_status, place_of_birth, father_fullname, date_of_birth,age,  mother_fullname, parents_residence, date_of_death, place_of_death, status, event_name, role) 
                VALUES (?, ?,?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssssssisssssss", $scheduleId, $d_fullname, $d_address, $d_gender, $cause_of_death, $marital_status, $place_of_birth, $father_fullname, $date_of_birth,$birthage,  $mother_fullname, $parents_residence, $date_of_death, $place_of_death, $status, $event_name, $role);
    
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function insertWeddingFill($scheduleId, $groom_name, $groom_dob, $groom_place_of_birth, $groom_citizenship, $groom_address, $groom_religion, $groom_previously_married,$groomage, $bride_name, $bride_dob, $bride_place_of_birth, $bride_citizenship, $bride_address, $bride_religion, $bride_previously_married,$brideage) {
      
    
        $sql = "INSERT INTO marriagefill (schedule_id, groom_name, groom_dob, groom_place_of_birth, groom_citizenship, groom_address, groom_religion, groom_previously_married,groom_age, bride_name, bride_dob, bride_place_of_birth, bride_citizenship, bride_address, bride_religion, bride_previously_married,bride_age, status, event_name, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?,?,?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Wedding','Online')";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssssssisssssssi", $scheduleId, $groom_name, $groom_dob, $groom_place_of_birth, $groom_citizenship, $groom_address, $groom_religion, $groom_previously_married,$groomage, $bride_name, $bride_dob, $bride_place_of_birth, $bride_citizenship, $bride_address, $bride_religion, $bride_previously_married,$brideage);
    
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getSchedule($date) {
        $sql = "
            SELECT s.`start_time`, s.`end_time`
            FROM `schedule` s
            INNER JOIN `baptismfill` b ON s.`schedule_id` = b.`schedule_id`
            WHERE s.`date` = ?
            
            UNION ALL
            
            SELECT s.`start_time`, s.`end_time`
            FROM `schedule` s
            INNER JOIN `confirmationfill` c ON s.`schedule_id` = c.`schedule_id`
            WHERE s.`date` = ?
            
            UNION ALL
            
            SELECT s.`start_time`, s.`end_time`
            FROM `schedule` s
            INNER JOIN `defuctomfill` d ON s.`schedule_id` = d.`schedule_id`
            WHERE s.`date` = ?
            
            UNION ALL
            
            SELECT s.`start_time`, s.`end_time`
            FROM `schedule` s
            INNER JOIN `marriagefill` m ON s.`schedule_id` = m.`schedule_id`
            WHERE s.`date` = ?
            
            UNION ALL
            
            SELECT 
            s.start_time, 
            s.end_time
        FROM schedule s
        INNER JOIN req_form rf ON s.schedule_id = rf.schedule_id
        WHERE rf.event_location = 'Inside' AND s.date = ?
            
            UNION ALL
            
            SELECT s.`start_time`, s.`end_time`
            FROM `schedule` s
            INNER JOIN `announcement` a ON s.`schedule_id` = a.`schedule_id`
            WHERE s.`date` = ?

            UNION ALL
            
            SELECT s.`start_time`, s.`end_time`
            FROM `schedule` s
            INNER JOIN `mass_Schedule` ms ON s.`schedule_id` = ms.`schedule_id`
            WHERE s.`date` = ?
        ";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssss", $date, $date, $date, $date, $date, $date, $date); // Bind the date parameter six times for each SELECT
        $stmt->execute();
        $result = $stmt->get_result();
        $schedules = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        return $schedules;
    }
    
    public function requestgetSchedule($date) {
        $sql = "
          
    
            SELECT s.`start_time`, s.`end_time`
            FROM `schedule` s
            INNER JOIN `req_form` r ON s.`schedule_id` = r.`schedule_id`
            WHERE s.`date` = ?
    
        ";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $date); // Bind the date parameter five times for each SELECT
        $stmt->execute();
        $result = $stmt->get_result();
        $schedules = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        return $schedules;
    }
   public function getAvailablePriests($selectedDate, $startTime, $endTime) {
    // Query to fetch available priests excluding those already booked on the selected date and time
    $sql = "
    SELECT 
        c.citizend_id,
        c.fullname
    FROM 
        citizen c
    WHERE 
        c.user_type = 'Priest' AND c.r_status = 'Active'
        AND NOT EXISTS (
            -- Combine all event types that involve priests using UNION to check if the priest is busy
            SELECT 1
            FROM (
                SELECT pa.priest_id
                FROM schedule s
                JOIN baptismfill b ON s.schedule_id = b.schedule_id
                LEFT JOIN priest_approval pa ON pa.approval_id = b.approval_id
                WHERE s.date = ? AND (s.start_time < ? AND s.end_time > ?)
                
                UNION ALL
                
                SELECT pa.priest_id
                FROM schedule s
                JOIN confirmationfill cf ON s.schedule_id = cf.schedule_id
                LEFT JOIN priest_approval pa ON pa.approval_id = cf.approval_id
                WHERE s.date = ? AND (s.start_time < ? AND s.end_time > ?)
                
                UNION ALL
                
                SELECT pa.priest_id
                FROM schedule s
                JOIN defuctomfill df ON s.schedule_id = df.schedule_id
                LEFT JOIN priest_approval pa ON pa.approval_id = df.approval_id
                WHERE s.date = ? AND (s.start_time < ? AND s.end_time > ?)
                
                UNION ALL
                
                SELECT pa.priest_id
                FROM schedule s
                JOIN marriagefill mf ON s.schedule_id = mf.schedule_id
                LEFT JOIN priest_approval pa ON pa.approval_id = mf.approval_id
                WHERE s.date = ? AND (s.start_time < ? AND s.end_time > ?)
                
                UNION ALL
                
                SELECT pa.priest_id
                FROM schedule s
                JOIN announcement ann ON s.schedule_id = ann.schedule_id
                LEFT JOIN priest_approval pa ON pa.approval_id = ann.approval_id
                WHERE s.date = ? AND (s.start_time < ? AND s.end_time > ?)
                
                UNION ALL
                
                SELECT pa.priest_id
                FROM schedule s
                JOIN req_form r ON s.schedule_id = r.schedule_id
                LEFT JOIN priest_approval pa ON pa.approval_id = r.approval_id
                WHERE s.date = ? AND (s.start_time < ? AND s.end_time > ?)
              
                UNION ALL
                
                SELECT pa.priest_id
                FROM schedule s
                JOIN mass_schedule ms ON s.schedule_id = ms.schedule_id
                LEFT JOIN priest_approval pa ON pa.approval_id = ms.approval_id
                WHERE s.date = ? AND (s.start_time < ? AND s.end_time > ?)
            ) AS events
            WHERE events.priest_id = c.citizend_id
        )
    ";

    // Prepare the statement
    $stmt = $this->conn->prepare($sql);
    
    // Bind the parameters for all event types
    $stmt->bind_param(
        "sssssssssssssssssssss",
        $selectedDate, $endTime, $startTime,  // Baptism
        $selectedDate, $endTime, $startTime,  // Confirmation
        $selectedDate, $endTime, $startTime,  // Defuctom
        $selectedDate, $endTime, $startTime,  // Marriage
        $selectedDate, $endTime, $startTime,  // Announcement
        $selectedDate, $endTime, $startTime,  // Mass
        $selectedDate, $endTime, $startTime   // Req Form
    );

    // Execute the statement
    $stmt->execute();
    
    // Fetch results
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Convert start_time and end_time to 12-hour format with AM/PM
    foreach ($result as &$row) {
        // Assuming the time fields are part of the fetched data
        $row['start_time'] = $this->convertTo12HourFormat($startTime);
        $row['end_time'] = $this->convertTo12HourFormat($endTime);
    }

    return $result;
}

// Helper function to convert time to 12-hour format
private function convertTo12HourFormat($time) {
    $dateTime = DateTime::createFromFormat('H:i:s', $time);
    return $dateTime ? $dateTime->format('g:i A') : $time; // Default to original if conversion fails
}

    
    
    
    
    
    
    
    
    public function getPriests() {
        $sql = "SELECT citizend_id, fullname FROM citizen WHERE user_type = 'Priest'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $priests = [];
        while ($row = $result->fetch_assoc()) {
            $priests[] = $row;
        }

        $stmt->close();
        return $priests;
    }
}
?>
