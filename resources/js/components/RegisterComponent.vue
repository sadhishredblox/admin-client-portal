<template>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Register Admin</div>

                <div class="card-body">
                    <form @submit.prevent="register">
                        <!-- First Name -->
                        <div class="row mb-3">
                            <label for="first_name" class="col-md-4 col-form-label text-md-end">
                                First Name <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <input 
                                    id="first_name" 
                                    type="text" 
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.first_name }"
                                    v-model="form.first_name" 
                                    required 
                                    autofocus
                                >
                                <span v-if="errors.first_name" class="invalid-feedback" role="alert">
                                    <strong>{{ errors.first_name[0] }}</strong>
                                </span>
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="row mb-3">
                            <label for="last_name" class="col-md-4 col-form-label text-md-end">
                                Last Name <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <input 
                                    id="last_name" 
                                    type="text" 
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.last_name }"
                                    v-model="form.last_name" 
                                    required
                                >
                                <span v-if="errors.last_name" class="invalid-feedback" role="alert">
                                    <strong>{{ errors.last_name[0] }}</strong>
                                </span>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <input 
                                    id="email" 
                                    type="email" 
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.email }"
                                    v-model="form.email" 
                                    required
                                >
                                <span v-if="errors.email" class="invalid-feedback" role="alert">
                                    <strong>{{ errors.email[0] }}</strong>
                                </span>
                            </div>
                        </div>

                        <!-- Password with Show/Hide Toggle and Strength Indicator -->
                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input 
                                        id="password" 
                                        :type="showPassword ? 'text' : 'password'" 
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.password || passwordStrengthClass === 'weak' }"
                                        v-model="form.password" 
                                        @input="checkPasswordStrength"
                                        required
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
                                <small class="form-text text-muted d-block mt-2">
                                    Password must contain:
                                    <ul class="mb-0 ps-3">
                                        <li :class="passwordChecks.length ? 'text-success' : 'text-muted'">
                                            <i :class="passwordChecks.length ? 'fas fa-check' : 'far fa-circle'"></i>
                                            At least 8 characters
                                        </li>
                                        <li :class="passwordChecks.uppercase ? 'text-success' : 'text-muted'">
                                            <i :class="passwordChecks.uppercase ? 'fas fa-check' : 'far fa-circle'"></i>
                                            One uppercase letter
                                        </li>
                                        <li :class="passwordChecks.lowercase ? 'text-success' : 'text-muted'">
                                            <i :class="passwordChecks.lowercase ? 'fas fa-check' : 'far fa-circle'"></i>
                                            One lowercase letter
                                        </li>
                                        <li :class="passwordChecks.number ? 'text-success' : 'text-muted'">
                                            <i :class="passwordChecks.number ? 'fas fa-check' : 'far fa-circle'"></i>
                                            One number
                                        </li>
                                        <li :class="passwordChecks.special ? 'text-success' : 'text-muted'">
                                            <i :class="passwordChecks.special ? 'fas fa-check' : 'far fa-circle'"></i>
                                            One special character (@$!%*?&)
                                        </li>
                                    </ul>
                                </small>

                                <span v-if="errors.password" class="invalid-feedback d-block" role="alert">
                                    <strong>{{ errors.password[0] }}</strong>
                                </span>
                            </div>
                        </div>

                        <!-- Confirm Password with Show/Hide Toggle -->
                        <div class="row mb-3">
                            <label for="password_confirmation" class="col-md-4 col-form-label text-md-end">
                                Confirm Password <span class="text-danger">*</span>
                            </label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input 
                                        id="password_confirmation" 
                                        :type="showConfirmPassword ? 'text' : 'password'" 
                                        class="form-control"
                                        v-model="form.password_confirmation" 
                                        required
                                    >
                                    <button 
                                        class="btn btn-outline-secondary" 
                                        type="button"
                                        @click="toggleConfirmPasswordVisibility"
                                    >
                                        <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                                <span v-if="!passwordsMatch && form.password_confirmation" class="text-danger small d-block mt-1">
                                    Passwords do not match
                                </span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button 
                                    type="submit" 
                                    class="btn btn-primary"
                                    :disabled="isSubmitting || !isPasswordStrong"
                                >
                                    <span v-if="isSubmitting">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Registering...
                                    </span>
                                    <span v-else>Register</span>
                                </button>

                                <a class="btn btn-link" href="/login">
                                    Already have an account?
                                </a>
                            </div>
                        </div>

                        <!-- Success Message -->
                        <div v-if="successMessage" class="row mt-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="alert alert-success" role="alert">
                                    {{ successMessage }}
                                </div>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div v-if="errorMessage" class="row mt-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="alert alert-danger" role="alert">
                                    {{ errorMessage }}
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'RegisterComponent',
    data() {
        return {
            form: {
                first_name: '',
                last_name: '',
                email: '',
                password: '',
                password_confirmation: ''
            },
            errors: {},
            isSubmitting: false,
            successMessage: '',
            errorMessage: '',
            showPassword: false,
            showConfirmPassword: false,
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
        isPasswordStrong() {
            return this.passwordStrength >= 3 && this.passwordsMatch;
        },
        passwordsMatch() {
            if (!this.form.password_confirmation) return true;
            return this.form.password === this.form.password_confirmation;
        }
    },
    methods: {
        togglePasswordVisibility() {
            this.showPassword = !this.showPassword;
        },
        toggleConfirmPasswordVisibility() {
            this.showConfirmPassword = !this.showConfirmPassword;
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
        validateStrongPassword() {
            const password = this.form.password;
            
            if (password.length < 8) {
                this.errors.password = ['Password must be at least 8 characters long'];
                return false;
            }
            
            if (!/[A-Z]/.test(password)) {
                this.errors.password = ['Password must contain at least one uppercase letter'];
                return false;
            }
            
            if (!/[a-z]/.test(password)) {
                this.errors.password = ['Password must contain at least one lowercase letter'];
                return false;
            }
            
            if (!/[0-9]/.test(password)) {
                this.errors.password = ['Password must contain at least one number'];
                return false;
            }
            
            if (!/[@$!%*?&]/.test(password)) {
                this.errors.password = ['Password must contain at least one special character (@$!%*?&)'];
                return false;
            }

            return true;
        },
        async register() {
            // Reset messages and errors
            this.errors = {};
            this.successMessage = '';
            this.errorMessage = '';
            this.isSubmitting = true;

            // Client-side validation
            if (!this.validateStrongPassword()) {
                this.isSubmitting = false;
                return;
            }

            // Check if passwords match
            if (this.form.password !== this.form.password_confirmation) {
                this.errors.password = ['Passwords do not match'];
                this.isSubmitting = false;
                return;
            }

            try {
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                const response = await axios.post('/register', this.form, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                console.log('Registration response:', response);
                
                // Success
                this.successMessage = 'Registration successful! Redirecting...';
                
                // Redirect to home after 1 second
                setTimeout(() => {
                    window.location.href = response.data.redirect || '/home';
                }, 1000);

            } catch (error) {
                this.isSubmitting = false;
                console.error('Registration error:', error);

                if (error.response) {
                    // Server returned error response
                    if (error.response.status === 422) {
                        // Validation errors
                        this.errors = error.response.data.errors || {};
                    } else {
                        // Other server errors
                        this.errorMessage = error.response.data.message || 'Registration failed. Please try again.';
                    }
                } else {
                    // Network or other errors
                    this.errorMessage = 'An error occurred. Please check your connection and try again.';
                }
            }
        }
    }
}
</script>

<style scoped>
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.2em;
}

.input-group .invalid-feedback {
    width: 100%;
}

ul {
    list-style-type: none;
}

ul li i {
    width: 15px;
    margin-right: 5px;
}
</style>