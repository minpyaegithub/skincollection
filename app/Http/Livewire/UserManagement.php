<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Clinic;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserManagement extends Component
{
    public $users = [];
    public $clinics = [];
    public $roles = [];
    public $search = '';
    public $showModal = false;
    public $editingUser = null;
    
    // User form fields
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $mobile_number = '';
    public $password = '';
    public $clinic_id = '';
    public $role = '';
    public $status = 1;

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'nullable|string|max:255',
        'email' => 'required|email|max:255|unique:users,email',
        'mobile_number' => 'nullable|string|max:20',
        'password' => 'required|string|min:8',
        'clinic_id' => 'required|exists:clinics,id',
        'role' => 'required|string',
        'status' => 'boolean',
    ];

    public function mount()
    {
        $this->clinics = Clinic::all();
        $this->roles = Role::all();
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $query = User::with(['clinic', 'roles']);
        
        // If user is not admin, only show users from their clinic
        if (!Auth::user()->isAdmin()) {
            $query->where('clinic_id', Auth::user()->clinic_id);
        }
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }
        
        $this->users = $query->orderBy('created_at', 'desc')->get();
    }

    public function updatedSearch()
    {
        $this->loadUsers();
    }

    public function showCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function showEditModal($userId)
    {
        $user = User::findOrFail($userId);
        $this->editingUser = $user;
        
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->mobile_number = $user->mobile_number;
        $this->clinic_id = $user->clinic_id;
        $this->role = $user->roles->first()->name ?? '';
        $this->status = $user->status;
        $this->password = ''; // Don't pre-fill password
        
        $this->showModal = true;
    }

    public function saveUser()
    {
        $rules = $this->rules;
        
        // If editing, don't require password and allow same email
        if ($this->editingUser) {
            $rules['password'] = 'nullable|string|min:8';
            $rules['email'] = 'required|email|max:255|unique:users,email,' . $this->editingUser->id;
        }
        
        $this->validate($rules);

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,
            'clinic_id' => $this->clinic_id,
            'status' => $this->status,
        ];

        // Only update password if provided
        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUser) {
            // Update existing user
            $this->editingUser->update($data);
            
            // Update role
            $this->editingUser->syncRoles([$this->role]);
            
            session()->flash('message', 'User updated successfully!');
        } else {
            // Create new user
            $data['password'] = Hash::make($this->password);
            $user = User::create($data);
            
            // Assign role
            $user->assignRole($this->role);
            
            session()->flash('message', 'User created successfully!');
        }

        $this->resetForm();
        $this->loadUsers();
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        
        // Don't allow deleting yourself
        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot delete your own account!');
            return;
        }
        
        $user->delete();
        session()->flash('message', 'User deleted successfully!');
        $this->loadUsers();
    }

    public function resetForm()
    {
        $this->editingUser = null;
        $this->first_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->mobile_number = '';
        $this->password = '';
        $this->clinic_id = '';
        $this->role = '';
        $this->status = 1;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.user-management')
            ->layout('layouts.app');
    }
}
