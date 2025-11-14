<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Client Management</h2>
            <button class="btn btn-primary" @click="showCreateModal">
                <i class="fas fa-plus"></i> Add New Client
            </button>
        </div>

        <!-- Clients Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact No</th>
                                <th>Birthday</th>
                                <th>Interests</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="clients.length === 0">
                                <td colspan="7" class="text-center">No clients found</td>
                            </tr>
                            <tr v-for="client in clients" :key="client.id">
                                <td>{{ client.id }}</td>
                                <td>{{ client.first_name }} {{ client.last_name }}</td>
                                <td>{{ client.email }}</td>
                                <td>{{ client.contact_no || 'N/A' }}</td>
                                <td>{{ formatDate(client.birthday) }}</td>
                                <td>
                                    <span v-for="interest in client.interests" :key="interest.id" class="badge bg-info me-1">
                                        {{ interest.name }}
                                    </span>
                                    <span v-if="!client.interests || client.interests.length === 0" class="text-muted">None</span>
                                </td>
                                <td>
                                    <button 
                                        class="btn btn-sm btn-warning me-1" 
                                        @click="showEditModal(client)"
                                        title="Edit Client"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        class="btn btn-sm btn-danger" 
                                        @click="deleteClient(client.id)"
                                        title="Delete Client"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div class="modal fade" id="clientModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ isEditing ? 'Edit Client' : 'Create New Client' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveClient">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        :class="{ 'is-invalid': errors.first_name }"
                                        v-model="form.first_name" 
                                        required
                                    >
                                    <div v-if="errors.first_name" class="invalid-feedback">{{ errors.first_name[0] }}</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        :class="{ 'is-invalid': errors.last_name }"
                                        v-model="form.last_name" 
                                        required
                                    >
                                    <div v-if="errors.last_name" class="invalid-feedback">{{ errors.last_name[0] }}</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    :class="{ 'is-invalid': errors.email }"
                                    v-model="form.email" 
                                    required
                                >
                                <div v-if="errors.email" class="invalid-feedback">{{ errors.email[0] }}</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Number</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        :class="{ 'is-invalid': errors.contact_no }"
                                        v-model="form.contact_no"
                                        @input="validateContactNumber"
                                        placeholder="Enter numbers only"
                                        maxlength="15"
                                    >
                                    <div v-if="errors.contact_no" class="invalid-feedback">{{ errors.contact_no[0] }}</div>
                                    <small class="form-text text-muted">Numbers only (max 15 digits)</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Birthday</label>
                                    <input 
                                        type="date" 
                                        class="form-control" 
                                        v-model="form.birthday"
                                        :max="maxDate"
                                    >
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Password 
                                    <span class="text-danger" v-if="!isEditing">*</span>
                                    <span class="text-muted small" v-if="isEditing">(leave blank to keep current)</span>
                                </label>
                                <div class="input-group">
                                    <input 
                                        :type="showPassword ? 'text' : 'password'" 
                                        class="form-control" 
                                        :class="{ 'is-invalid': errors.password || (form.password && passwordStrengthClass === 'weak') }"
                                        v-model="form.password" 
                                        @input="checkPasswordStrength"
                                        :required="!isEditing"
                                    >
                                    <button 
                                        class="btn btn-outline-secondary" 
                                        type="button"
                                        @click="togglePasswordVisibility"
                                    >
                                        <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>

                                <!-- Password Strength Indicator -->
                                <div v-if="form.password" class="mt-2">
                                    <div class="progress" style="height: 5px;">
                                        <div 
                                            class="progress-bar" 
                                            :class="passwordStrengthColor"
                                            :style="{ width: passwordStrengthPercentage + '%' }"
                                        ></div>
                                    </div>
                                    <small :class="'text-' + passwordStrengthColor.replace('bg-', '')">
                                        Password strength: {{ passwordStrengthText }}
                                    </small>
                                </div>

                                <!-- Password Requirements -->
                                <small class="form-text text-muted d-block mt-2" v-if="!isEditing || form.password">
                                    Password must contain:
                                    <ul class="mb-0 ps-3 password-requirements">
                                        <li :class="passwordChecks.length ? 'text-success' : 'text-muted'">
                                            <i :class="passwordChecks.length ? 'fas fa-check-circle' : 'far fa-circle'"></i>
                                            At least 8 characters
                                        </li>
                                        <li :class="passwordChecks.uppercase ? 'text-success' : 'text-muted'">
                                            <i :class="passwordChecks.uppercase ? 'fas fa-check-circle' : 'far fa-circle'"></i>
                                            One uppercase letter
                                        </li>
                                        <li :class="passwordChecks.lowercase ? 'text-success' : 'text-muted'">
                                            <i :class="passwordChecks.lowercase ? 'fas fa-check-circle' : 'far fa-circle'"></i>
                                            One lowercase letter
                                        </li>
                                        <li :class="passwordChecks.number ? 'text-success' : 'text-muted'">
                                            <i :class="passwordChecks.number ? 'fas fa-check-circle' : 'far fa-circle'"></i>
                                            One number
                                        </li>
                                        <li :class="passwordChecks.special ? 'text-success' : 'text-muted'">
                                            <i :class="passwordChecks.special ? 'fas fa-check-circle' : 'far fa-circle'"></i>
                                            One special character (@$!%*?&)
                                        </li>
                                    </ul>
                                </small>

                                <div v-if="errors.password" class="invalid-feedback d-block">{{ errors.password[0] }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Interests</label>
                                <div class="row">
                                    <div class="col-md-6" v-for="interest in interests" :key="interest.id">
                                        <div class="form-check">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                :value="interest.id" 
                                                v-model="form.interests" 
                                                :id="'interest-' + interest.id"
                                            >
                                            <label class="form-check-label" :for="'interest-' + interest.id">
                                                {{ interest.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button 
                                    type="submit" 
                                    class="btn btn-primary"
                                    :disabled="!canSubmit"
                                >
                                    <span v-if="isSubmitting">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        {{ isEditing ? 'Updating...' : 'Creating...' }}
                                    </span>
                                    <span v-else>
                                        {{ isEditing ? 'Update' : 'Create' }} Client
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Modal } from 'bootstrap';
import { useToast } from "vue-toastification";

export default {
    name: 'ClientManagement',
    props: {
        initialClients: {
            type: Array,
            required: true
        },
        interests: {
            type: Array,
            required: true
        }
    },
    setup() {
        const toast = useToast();
        return { toast };
    },
    data() {
        return {
            clients: [],
            isEditing: false,
            isSubmitting: false,
            currentClientId: null,
            form: {
                first_name: '',
                last_name: '',
                email: '',
                contact_no: '',
                birthday: '',
                password: '',
                interests: []
            },
            errors: {},
            modal: null,
            showPassword: false,
            passwordStrength: 0,
            passwordChecks: {
                length: false,
                uppercase: false,
                lowercase: false,
                number: false,
                special: false
            }
        }
    },
    computed: {
        maxDate() {
            // Set max date to today
            const today = new Date();
            return today.toISOString().split('T')[0];
        },
        passwordStrengthText() {
            if (this.passwordStrength === 0) return 'Very Weak';
            if (this.passwordStrength === 1) return 'Weak';
            if (this.passwordStrength === 2) return 'Fair';
            if (this.passwordStrength === 3) return 'Good';
            if (this.passwordStrength === 4) return 'Strong';
            return 'Very Strong';
        },
        passwordStrengthColor() {
            if (this.passwordStrength === 0) return 'bg-danger';
            if (this.passwordStrength === 1) return 'bg-danger';
            if (this.passwordStrength === 2) return 'bg-warning';
            if (this.passwordStrength === 3) return 'bg-info';
            if (this.passwordStrength === 4) return 'bg-success';
            return 'bg-success';
        },
        passwordStrengthClass() {
            if (this.passwordStrength <= 2) return 'weak';
            return 'strong';
        },
        passwordStrengthPercentage() {
            return (this.passwordStrength / 5) * 100;
        },
        isPasswordValid() {
            if (!this.form.password) {
                return this.isEditing; 
            }
            return this.passwordStrength >= 3;
        },
        canSubmit() {
            if (this.isSubmitting) return false;
            if (!this.isEditing && !this.form.password) return false;
            if (this.form.password && !this.isPasswordValid) return false;
            return true;
        }
    },
    mounted() {
        this.clients = [...this.initialClients];
        this.modal = new Modal(document.getElementById('clientModal'));
    },
    methods: {
        togglePasswordVisibility() {
            this.showPassword = !this.showPassword;
        },
        validateContactNumber() {
            // Remove any non-numeric characters
            this.form.contact_no = this.form.contact_no.replace(/[^0-9]/g, '');
        },
        checkPasswordStrength() {
            const password = this.form.password;
            
            // Reset checks
            this.passwordChecks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[@$!%*?&]/.test(password)
            };

            // Calculate strength (0-5)
            this.passwordStrength = Object.values(this.passwordChecks).filter(Boolean).length;
        },
        showCreateModal() {
            this.isEditing = false;
            this.currentClientId = null;
            this.resetForm();
            this.modal.show();
        },
        showEditModal(client) {
            this.isEditing = true;
            this.currentClientId = client.id;
            this.form = {
                first_name: client.first_name,
                last_name: client.last_name,
                email: client.email,
                contact_no: client.contact_no || '',
                birthday: client.birthday || '',
                password: '',
                interests: client.interests.map(i => i.id)
            };
            this.errors = {};
            this.passwordStrength = 0;
            this.modal.show();
        },
        async saveClient() {
            this.errors = {};
            this.isSubmitting = true;
            
            try {
                if (this.isEditing) {
                    await this.updateClient();
                } else {
                    await this.createClient();
                }
            } catch (error) {
                if (error.response && error.response.data.errors) {
                    this.errors = error.response.data.errors;
                    this.toast.error('Please fix the validation errors');
                } else {
                    this.toast.error('An error occurred. Please try again.');
                }
            } finally {
                this.isSubmitting = false;
            }
        },
        async createClient() {
            const response = await axios.post('/clients', this.form);
            this.clients.unshift(response.data.client);
            this.modal.hide();
            this.resetForm();
            this.toast.success('Client created successfully!');
        },
        async updateClient() {
            const response = await axios.put(`/clients/${this.currentClientId}`, this.form);
            const index = this.clients.findIndex(c => c.id === this.currentClientId);
            if (index !== -1) {
                this.clients[index] = response.data.client;
            }
            this.modal.hide();
            this.resetForm();
            this.toast.success('Client updated successfully!');
        },
        async deleteClient(id) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                await axios.delete(`/clients/${id}`);
                this.clients = this.clients.filter(c => c.id !== id);
                this.toast.success('Client deleted successfully!');
            } catch (error) {
                this.toast.error('An error occurred while deleting the client.');
            }
        },
        resetForm() {
            this.form = {
                first_name: '',
                last_name: '',
                email: '',
                contact_no: '',
                birthday: '',
                password: '',
                interests: []
            };
            this.errors = {};
            this.passwordStrength = 0;
            this.showPassword = false;
        },
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString();
        }
    }
}
</script>

<style scoped>
.password-requirements {
    list-style-type: none;
    padding-left: 0;
}

.password-requirements li {
    font-size: 0.85rem;
}

.password-requirements li i {
    width: 15px;
    margin-right: 5px;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.2em;
}
</style>