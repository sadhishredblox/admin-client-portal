<template>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Admin Login</div>

                <div class="card-body">
                    <!-- Display session error (for client login attempts) -->
                    <div v-if="sessionError" class="alert alert-danger" role="alert">
                        {{ sessionError }}
                    </div>

                    <form @submit.prevent="login">
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
                                    autofocus
                                >
                                <span v-if="errors.email" class="invalid-feedback" role="alert">
                                    <strong>{{ errors.email[0] }}</strong>
                                </span>
                            </div>
                        </div>

                        <!-- Password with Show/Hide Toggle -->
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
                                        :class="{ 'is-invalid': errors.password }"
                                        v-model="form.password" 
                                        required
                                    >
                                    <button 
                                        class="btn btn-outline-secondary" 
                                        type="button"
                                        @click="togglePasswordVisibility"
                                    >
                                        <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                    <span v-if="errors.password" class="invalid-feedback d-block" role="alert">
                                        <strong>{{ errors.password[0] }}</strong>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        id="remember"
                                        v-model="form.remember"
                                    >
                                    <label class="form-check-label" for="remember">
                                        Remember Me
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button 
                                    type="submit" 
                                    class="btn btn-primary"
                                    :disabled="isSubmitting"
                                >
                                    <span v-if="isSubmitting">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Logging in...
                                    </span>
                                    <span v-else>Login</span>
                                </button>

                                <a class="btn btn-link" href="/register">
                                    Don't have an account?
                                </a>
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
    name: 'LoginComponent',
    data() {
        return {
            form: {
                email: '',
                password: '',
                remember: false
            },
            errors: {},
            isSubmitting: false,
            errorMessage: '',
            sessionError: '',
            showPassword: false
        }
    },
    mounted() {
        // Check for Laravel session error
        const metaError = document.querySelector('meta[name="session-error"]');
        if (metaError) {
            this.sessionError = metaError.getAttribute('content');
        }
    },
    methods: {
        togglePasswordVisibility() {
            this.showPassword = !this.showPassword;
        },
        async login() {
            // Reset messages and errors
            this.errors = {};
            this.errorMessage = '';
            this.isSubmitting = true;

            try {
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                console.log('Making API request...');
                
                const response = await axios.post('/login', this.form, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                console.log('Login response:', response);
                
                // Success - redirect to home
                window.location.href = response.data.redirect || '/home';

            } catch (error) {
                this.isSubmitting = false;
                console.error('Login error:', error);

                if (error.response) {
                    // Check if we got a new CSRF token (for client login attempts)
                    if (error.response.data.csrf_token) {
                        // Update the CSRF token in the meta tag
                        document.querySelector('meta[name="csrf-token"]').setAttribute('content', error.response.data.csrf_token);
                        
                        // Update axios default header
                        axios.defaults.headers.common['X-CSRF-TOKEN'] = error.response.data.csrf_token;
                    }

                    if (error.response.status === 422) {
                        // Validation errors
                        this.errors = error.response.data.errors || {};
                        this.errorMessage = error.response.data.message || 'Please check your credentials.';
                    } else if (error.response.status === 403) {
                        // Forbidden (client trying to login)
                        this.errorMessage = error.response.data.message || 'Access denied.';
                    } else if (error.response.data.message) {
                        // Other errors with message
                        this.errorMessage = error.response.data.message;
                    } else {
                        this.errorMessage = 'Login failed. Please try again.';
                    }
                } else {
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
</style>