<?php

/**
 * Document Model
 * Handles data operations for the 'document' table.
 */
class Document {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Creates a new document record.
     *
     * @param array $data The document data to insert.
     * @return int|false The ID of the newly created document, or false if creation failed.
     */
    public function createDocument(array $data): int|false {
        $this->db->query("INSERT INTO document (business_id, uploaded_by, document_type, document_name, file_path, notes, status, created_at, updated_at) 
                        VALUES (:business_id, :uploaded_by, :document_type, :document_name, :file_path, :notes, :status, NOW(), NOW())");
        
        // Bind values
        $this->db->bind(':business_id', $data['business_id']);
        $this->db->bind(':uploaded_by', $data['uploaded_by']);
        $this->db->bind(':document_type', $data['document_type']);
        $this->db->bind(':document_name', $data['document_name']);
        $this->db->bind(':file_path', $data['file_path']);
        $this->db->bind(':notes', $data['notes']);
        $this->db->bind(':status', $data['status']);
        
        // Execute
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Gets a document by ID
     *
     * @param int $id The document ID
     * @return object|false The document object, or false if not found
     */
    public function getDocumentById(int $id): object|false {
        $this->db->query("SELECT * FROM document WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Gets all documents for a specific business
     *
     * @param int $businessId The business ID
     * @return array The array of document objects
     */
    public function getDocumentsByBusinessId(int $businessId): array {
        $this->db->query("SELECT * FROM document WHERE business_id = :business_id ORDER BY created_at DESC");
        $this->db->bind(':business_id', $businessId);
        $results = $this->db->resultSet();
        return $results ?: [];
    }

    /**
     * Gets all pending documents (for admin/treasurer verification)
     *
     * @return array The array of pending document objects
     */
    public function getPendingDocuments(): array {
        $this->db->query("SELECT d.*, b.name as business_name, b.type as business_type, 
                         u.first_name as uploader_first_name, u.last_name as uploader_last_name 
                         FROM document d 
                         JOIN business b ON d.business_id = b.id 
                         JOIN user u ON d.uploaded_by = u.id 
                         WHERE d.status = 'Pending' 
                         ORDER BY d.created_at ASC");
        $results = $this->db->resultSet();
        return $results ?: [];
    }

    /**
     * Updates the status of a document
     *
     * @param int $id The document ID
     * @param string $status The new status ('Approved' or 'Rejected')
     * @param int $verifierId The ID of the user verifying the document
     * @param string $notes Optional notes for the verification
     * @return bool True if successful, false otherwise
     */
    public function updateDocumentStatus(int $id, string $status, int $verifierId, string $notes = ''): bool {
        $this->db->query("UPDATE document 
                        SET status = :status, 
                            verified_by = :verified_by, 
                            verification_notes = :notes, 
                            verified_at = NOW(), 
                            updated_at = NOW() 
                        WHERE id = :id");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        $this->db->bind(':verified_by', $verifierId);
        $this->db->bind(':notes', $notes);
        
        return $this->db->execute();
    }

    /**
     * Deletes a document record
     *
     * @param int $id The document ID
     * @return bool True if successful, false otherwise
     */
    public function deleteDocument(int $id): bool {
        $this->db->query("DELETE FROM document WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Gets a list of required documents based on business type
     *
     * @param string $businessType The type of business
     * @return array List of required documents with metadata
     */
    public function getRequiredDocuments(string $businessType = ''): array {
        // Base document types required for all businesses
        $documentTypes = [
            'barangay_clearance' => [
                'name' => 'Barangay Clearance',
                'required' => true,
                'description' => 'Clearance from the barangay where the business is located'
            ],
            'dti_registration' => [
                'name' => 'DTI Business Registration',
                'required' => true,
                'description' => 'Department of Trade and Industry business name registration certificate'
            ],
            'valid_id' => [
                'name' => 'Valid Government ID',
                'required' => true,
                'description' => 'Any valid government-issued ID of the business owner'
            ],
            'lease_contract' => [
                'name' => 'Lease Contract/Proof of Ownership',
                'required' => true,
                'description' => 'Proof of property lease or ownership where the business operates'
            ]
        ];
        
        // Add business type specific documents
        switch (strtolower($businessType)) {
            case 'food':
                $documentTypes['sanitary_permit'] = [
                    'name' => 'Sanitary Permit',
                    'required' => true,
                    'description' => 'Health and sanitation permit for food businesses'
                ];
                break;
                
            case 'retail':
            case 'wholesale':
                $documentTypes['supplier_contract'] = [
                    'name' => 'Supplier Contract/List',
                    'required' => false,
                    'description' => 'Documentation of your business suppliers'
                ];
                break;
                
            case 'service':
                $documentTypes['professional_license'] = [
                    'name' => 'Professional License/Certificate',
                    'required' => false,
                    'description' => 'Professional license if offering licensed services'
                ];
                break;
                
            case 'manufacturing':
                $documentTypes['environmental_clearance'] = [
                    'name' => 'Environmental Clearance',
                    'required' => true,
                    'description' => 'Environmental compliance certificate for manufacturing businesses'
                ];
                $documentTypes['fire_safety'] = [
                    'name' => 'Fire Safety Inspection Certificate',
                    'required' => true,
                    'description' => 'Fire safety certificate for manufacturing facilities'
                ];
                break;
        }
        
        // Optional documents for all business types
        $documentTypes['tax_declaration'] = [
            'name' => 'Tax Declaration',
            'required' => false,
            'description' => 'Latest tax declaration of the property where the business operates'
        ];
        
        return $documentTypes;
    }

    /**
     * Get all missing required documents for a business
     *
     * @param int $businessId Business ID
     * @param string $businessType Business type
     * @return array Array of missing document types
     */
    public function getMissingRequiredDocuments(int $businessId, string $businessType): array {
        // Get all required document types for this business type
        $requiredTypes = array_filter($this->getRequiredDocuments($businessType), function($doc) {
            return $doc['required'] === true;
        });
        
        // Get all approved and pending documents for this business
        $this->db->query("SELECT document_type FROM document 
                         WHERE business_id = :business_id AND (status = 'Approved' OR status = 'Pending')");
        $this->db->bind(':business_id', $businessId);
        $submittedDocs = $this->db->resultSet();
        
        // Create array of submitted document types
        $submittedTypes = [];
        foreach ($submittedDocs as $doc) {
            $submittedTypes[] = $doc->document_type;
        }
        
        // Filter out submitted document types
        $missingTypes = [];
        foreach ($requiredTypes as $type => $info) {
            if (!in_array($type, $submittedTypes)) {
                $missingTypes[$type] = $info;
            }
        }
        
        return $missingTypes;
    }

    /**
     * Check if all required documents for a business are approved
     *
     * @param int $businessId Business ID
     * @param string $businessType Business type
     * @return bool True if all required documents are approved, false otherwise
     */
    public function areAllRequiredDocumentsApproved(int $businessId, string $businessType): bool {
        // Get all required document types
        $requiredDocs = array_filter($this->getRequiredDocuments($businessType), function($doc) {
            return $doc['required'] === true;
        });
        
        if (empty($requiredDocs)) {
            return true;
        }
        
        $requiredTypes = array_keys($requiredDocs);
        
        // Get approved documents
        $this->db->query("SELECT document_type FROM document 
                         WHERE business_id = :business_id AND status = 'Approved'");
        $this->db->bind(':business_id', $businessId);
        $approvedDocs = $this->db->resultSet();
        
        // Create array of approved document types
        $approvedTypes = [];
        foreach ($approvedDocs as $doc) {
            $approvedTypes[] = $doc->document_type;
        }
        
        // Check if all required types are in approved types
        foreach ($requiredTypes as $type) {
            if (!in_array($type, $approvedTypes)) {
                return false;
            }
        }
        
        return true;
    }
}