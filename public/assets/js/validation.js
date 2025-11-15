/**
 * Client-Side Validation Library
 * Production-ready form validation with real-time feedback
 */

const FormValidator = {
    /**
     * Email validation
     */
    validateEmail(email) {
        const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return re.test(email);
    },

    /**
     * Password strength validation
     */
    validatePassword(password, firstName = '', lastName = '', email = '') {
        const errors = [];

        if (password.length < 9) {
            errors.push('Password must be at least 9 characters long');
        }

        if (!/[A-Z]/.test(password)) {
            errors.push('Password must contain at least one uppercase letter');
        }

        if (!/[0-9]/.test(password)) {
            errors.push('Password must contain at least one number');
        }

        if (!/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/;~`]/.test(password)) {
            errors.push('Password must contain at least one special character');
        }

        if (firstName && password.toLowerCase().includes(firstName.toLowerCase())) {
            errors.push('Password cannot contain your first name');
        }

        if (lastName && password.toLowerCase().includes(lastName.toLowerCase())) {
            errors.push('Password cannot contain your last name');
        }

        if (email) {
            const emailPart = email.split('@')[0];
            if (password.toLowerCase().includes(emailPart.toLowerCase())) {
                errors.push('Password cannot contain your email address');
            }
        }

        const commonPasswords = [
            'password', 'password123', '123456', '12345678', 'qwerty', 'abc123',
            'password1', 'letmein', 'welcome', 'monkey', 'admin', 'login',
            'welcome123', 'admin123', 'root', 'pass123', 'Password1', 'Password123'
        ];

        if (commonPasswords.includes(password.toLowerCase())) {
            errors.push('This password is too common');
        }

        return {
            valid: errors.length === 0,
            errors: errors,
            strength: this.calculatePasswordStrength(password)
        };
    },

    /**
     * Calculate password strength (0-100)
     */
    calculatePasswordStrength(password) {
        let strength = 0;

        if (password.length >= 9) strength += 20;
        if (password.length >= 12) strength += 10;
        if (password.length >= 16) strength += 10;

        if (/[a-z]/.test(password)) strength += 10;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/[0-9]/.test(password)) strength += 15;
        if (/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/;~`]/.test(password)) strength += 20;

        const uniqueChars = new Set(password).size;
        if (uniqueChars > 8) strength += 10;

        return Math.min(100, strength);
    },

    /**
     * Get password strength label
     */
    getPasswordStrengthLabel(strength) {
        if (strength < 40) return { label: 'Weak', class: 'weak', color: '#f56565' };
        if (strength < 60) return { label: 'Fair', class: 'fair', color: '#ed8936' };
        if (strength < 80) return { label: 'Good', class: 'good', color: '#ecc94b' };
        return { label: 'Strong', class: 'strong', color: '#48bb78' };
    },

    /**
     * Validate required field
     */
    validateRequired(value) {
        return value !== null && value !== undefined && value.trim() !== '';
    },

    /**
     * Validate minimum length
     */
    validateMinLength(value, minLength) {
        return value.length >= minLength;
    },

    /**
     * Validate maximum length
     */
    validateMaxLength(value, maxLength) {
        return value.length <= maxLength;
    },

    /**
     * Validate pattern (regex)
     */
    validatePattern(value, pattern) {
        return pattern.test(value);
    },

    /**
     * Validate number
     */
    validateNumber(value) {
        return !isNaN(value) && !isNaN(parseFloat(value));
    },

    /**
     * Validate phone number
     */
    validatePhone(phone) {
        const re = /^[\d\s\-\+\(\)]+$/;
        return re.test(phone) && phone.replace(/\D/g, '').length >= 10;
    },

    /**
     * Validate URL
     */
    validateURL(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    },

    /**
     * Show error message for field
     */
    showError(field, message) {
        this.clearError(field);

        field.classList.add('is-invalid');

        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.cssText = 'color: #f56565; font-size: 13px; margin-top: 5px;';
        errorDiv.textContent = message;

        field.parentNode.appendChild(errorDiv);
    },

    /**
     * Clear error message for field
     */
    clearError(field) {
        field.classList.remove('is-invalid');

        const errorMsg = field.parentNode.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    },

    /**
     * Show success state for field
     */
    showSuccess(field) {
        this.clearError(field);
        field.classList.add('is-valid');
    },

    /**
     * Validate form on submit
     */
    validateForm(form) {
        let isValid = true;
        const fields = form.querySelectorAll('[data-validate]');

        fields.forEach(field => {
            const rules = field.dataset.validate.split('|');

            for (const rule of rules) {
                const [ruleName, ruleValue] = rule.split(':');

                switch (ruleName) {
                    case 'required':
                        if (!this.validateRequired(field.value)) {
                            this.showError(field, 'This field is required');
                            isValid = false;
                            return;
                        }
                        break;

                    case 'email':
                        if (field.value && !this.validateEmail(field.value)) {
                            this.showError(field, 'Please enter a valid email address');
                            isValid = false;
                            return;
                        }
                        break;

                    case 'min':
                        if (field.value && !this.validateMinLength(field.value, parseInt(ruleValue))) {
                            this.showError(field, `Minimum ${ruleValue} characters required`);
                            isValid = false;
                            return;
                        }
                        break;

                    case 'max':
                        if (field.value && !this.validateMaxLength(field.value, parseInt(ruleValue))) {
                            this.showError(field, `Maximum ${ruleValue} characters allowed`);
                            isValid = false;
                            return;
                        }
                        break;

                    case 'number':
                        if (field.value && !this.validateNumber(field.value)) {
                            this.showError(field, 'Please enter a valid number');
                            isValid = false;
                            return;
                        }
                        break;

                    case 'phone':
                        if (field.value && !this.validatePhone(field.value)) {
                            this.showError(field, 'Please enter a valid phone number');
                            isValid = false;
                            return;
                        }
                        break;

                    case 'url':
                        if (field.value && !this.validateURL(field.value)) {
                            this.showError(field, 'Please enter a valid URL');
                            isValid = false;
                            return;
                        }
                        break;
                }
            }

            this.showSuccess(field);
        });

        return isValid;
    },

    /**
     * Initialize real-time validation
     */
    initRealTimeValidation() {
        const fields = document.querySelectorAll('[data-validate]');

        fields.forEach(field => {
            field.addEventListener('blur', () => {
                const rules = field.dataset.validate.split('|');

                for (const rule of rules) {
                    const [ruleName, ruleValue] = rule.split(':');

                    switch (ruleName) {
                        case 'required':
                            if (!this.validateRequired(field.value)) {
                                this.showError(field, 'This field is required');
                                return;
                            }
                            break;

                        case 'email':
                            if (field.value && !this.validateEmail(field.value)) {
                                this.showError(field, 'Please enter a valid email address');
                                return;
                            }
                            break;
                    }
                }

                this.clearError(field);
            });

            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid')) {
                    this.clearError(field);
                }
            });
        });
    },

    /**
     * Password strength meter
     */
    attachPasswordStrengthMeter(passwordField, strengthBar, strengthLabel) {
        passwordField.addEventListener('input', () => {
            const password = passwordField.value;
            const strength = this.calculatePasswordStrength(password);
            const info = this.getPasswordStrengthLabel(strength);

            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor = info.color;
            strengthLabel.textContent = info.label;
            strengthLabel.style.color = info.color;
        });
    },

    /**
     * Confirm password match
     */
    attachPasswordConfirmation(passwordField, confirmField) {
        confirmField.addEventListener('blur', () => {
            if (confirmField.value && confirmField.value !== passwordField.value) {
                this.showError(confirmField, 'Passwords do not match');
            } else {
                this.clearError(confirmField);
            }
        });
    }
};

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    FormValidator.initRealTimeValidation();

    // Auto-attach to forms
    document.querySelectorAll('form[data-validate-form]').forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!FormValidator.validateForm(form)) {
                e.preventDefault();
            }
        });
    });
});
