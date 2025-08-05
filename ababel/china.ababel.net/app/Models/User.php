<?php
namespace App\Models;

use App\Core\Model;
use App\Core\SecurityManager;
use Exception;

/**
 * User Model
 * Handles user-related database operations with proper validation and security
 */
class User extends Model
{
    protected $table = 'users';
    private $security;
    
    public function __construct()
    {
        parent::__construct();
        $this->security = SecurityManager::getInstance();
    }
    
    /**
     * Find user by username
     */
    public function findByUsername($username)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE username = ? AND is_active = 1";
            $stmt = $this->db->query($sql, [$username]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error finding user by username: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = ? AND is_active = 1";
            $stmt = $this->db->query($sql, [$email]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error finding user by email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update last login timestamp
     */
    public function updateLastLogin($userId)
    {
        try {
            $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE id = ?";
            return $this->db->query($sql, [$userId]);
        } catch (Exception $e) {
            error_log("Error updating last login: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new user with validation
     */
    public function createUser($data)
    {
        try {
            // Validate required fields
            $requiredFields = ['username', 'password', 'full_name', 'email', 'role'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Validate email format
            if (!$this->security->validateEmail($data['email'])) {
                throw new Exception('Invalid email format');
            }
            
            // Validate password strength
            $passwordErrors = $this->security->validatePasswordStrength($data['password']);
            if (!empty($passwordErrors)) {
                throw new Exception('Password does not meet security requirements: ' . implode(', ', $passwordErrors));
            }
            
            // Check if username already exists
            if ($this->findByUsername($data['username'])) {
                throw new Exception('Username already exists');
            }
            
            // Check if email already exists
            if ($this->findByEmail($data['email'])) {
                throw new Exception('Email address already exists');
            }
            
            // Hash password before saving
            $data['password'] = $this->security->hashPassword($data['password']);
            
            // Set default values
            $data['is_active'] = $data['is_active'] ?? 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            return $this->create($data);
            
        } catch (Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Change user password
     */
    public function changePassword($userId, $newPassword)
    {
        try {
            // Validate password strength
            $passwordErrors = $this->security->validatePasswordStrength($newPassword);
            if (!empty($passwordErrors)) {
                throw new Exception('Password does not meet security requirements: ' . implode(', ', $passwordErrors));
            }
            
            $hashedPassword = $this->security->hashPassword($newPassword);
            $sql = "UPDATE {$this->table} SET password = ?, updated_at = NOW() WHERE id = ?";
            return $this->db->query($sql, [$hashedPassword, $userId]);
            
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data)
    {
        try {
            // Remove sensitive fields that shouldn't be updated via profile
            unset($data['password'], $data['role'], $data['is_active']);
            
            // Validate email if provided
            if (isset($data['email']) && !$this->security->validateEmail($data['email'])) {
                throw new Exception('Invalid email format');
            }
            
            // Check if email is already taken by another user
            if (isset($data['email'])) {
                $existingUser = $this->findByEmail($data['email']);
                if ($existingUser && $existingUser['id'] != $userId) {
                    throw new Exception('Email address is already in use');
                }
            }
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            return $this->update($userId, $data);
            
        } catch (Exception $e) {
            error_log("Error updating profile: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all active users
     */
    public function getActiveUsers()
    {
        try {
            $sql = "SELECT id, username, full_name, email, role, last_login, created_at 
                    FROM {$this->table} 
                    WHERE is_active = 1 
                    ORDER BY full_name";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting active users: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        try {
            $sql = "SELECT id, username, full_name, email, last_login 
                    FROM {$this->table} 
                    WHERE role = ? AND is_active = 1 
                    ORDER BY full_name";
            $stmt = $this->db->query($sql, [$role]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting users by role: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Deactivate user account
     */
    public function deactivateUser($userId)
    {
        try {
            $sql = "UPDATE {$this->table} SET is_active = 0, updated_at = NOW() WHERE id = ?";
            return $this->db->query($sql, [$userId]);
        } catch (Exception $e) {
            error_log("Error deactivating user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Activate user account
     */
    public function activateUser($userId)
    {
        try {
            $sql = "UPDATE {$this->table} SET is_active = 1, updated_at = NOW() WHERE id = ?";
            return $this->db->query($sql, [$userId]);
        } catch (Exception $e) {
            error_log("Error activating user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_users,
                        COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_users,
                        COUNT(CASE WHEN is_active = 0 THEN 1 END) as inactive_users,
                        COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_count,
                        COUNT(CASE WHEN role = 'accountant' THEN 1 END) as accountant_count,
                        COUNT(CASE WHEN role = 'viewer' THEN 1 END) as viewer_count
                    FROM {$this->table}";
            $stmt = $this->db->query($sql);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting user stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search users
     */
    public function searchUsers($searchTerm, $role = null, $limit = 50)
    {
        try {
            $sql = "SELECT id, username, full_name, email, role, last_login, is_active 
                    FROM {$this->table} 
                    WHERE (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
            $params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
            
            if ($role) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }
            
            $sql .= " ORDER BY full_name LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error searching users: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user activity log
     */
    public function getUserActivity($userId, $limit = 50)
    {
        try {
            $sql = "SELECT action, description, ip_address, created_at 
                    FROM activity_log 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $stmt = $this->db->query($sql, [$userId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting user activity: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate user data
     */
    public function validateUserData($data, $isUpdate = false)
    {
        $errors = [];
        
        // Required fields for new users
        if (!$isUpdate) {
            if (empty($data['username'])) {
                $errors[] = 'Username is required';
            }
            if (empty($data['password'])) {
                $errors[] = 'Password is required';
            }
        }
        
        // Validate email
        if (!empty($data['email']) && !$this->security->validateEmail($data['email'])) {
            $errors[] = 'Invalid email format';
        }
        
        // Validate password strength for new passwords
        if (!empty($data['password']) && !$isUpdate) {
            $passwordErrors = $this->security->validatePasswordStrength($data['password']);
            $errors = array_merge($errors, $passwordErrors);
        }
        
        // Validate role
        $validRoles = ['admin', 'accountant', 'viewer'];
        if (!empty($data['role']) && !in_array($data['role'], $validRoles)) {
            $errors[] = 'Invalid role specified';
        }
        
        return $errors;
    }
}