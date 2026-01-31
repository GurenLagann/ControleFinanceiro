/**
 * Validacao Client-Side para Formularios
 */

(function() {
    'use strict';

    // Configuracoes de validacao
    const validationRules = {
        required: {
            validate: (value) => value !== null && value !== undefined && value.toString().trim() !== '',
            message: 'Este campo e obrigatorio'
        },
        minLength: {
            validate: (value, min) => value.length >= min,
            message: (min) => `Minimo de ${min} caracteres`
        },
        maxLength: {
            validate: (value, max) => value.length <= max,
            message: (max) => `Maximo de ${max} caracteres`
        },
        min: {
            validate: (value, min) => parseFloat(value) >= min,
            message: (min) => `Valor minimo: ${min}`
        },
        max: {
            validate: (value, max) => parseFloat(value) <= max,
            message: (max) => `Valor maximo: ${max}`
        },
        numeric: {
            validate: (value) => !isNaN(parseFloat(value)) && isFinite(value),
            message: 'Informe um numero valido'
        },
        date: {
            validate: (value) => !isNaN(Date.parse(value)),
            message: 'Data invalida'
        },
        dateAfter: {
            validate: (value, compareField) => {
                const compareValue = document.querySelector(`[name="${compareField}"]`)?.value;
                if (!compareValue) return true;
                return new Date(value) >= new Date(compareValue);
            },
            message: 'A data deve ser igual ou posterior'
        },
        money: {
            validate: (value) => {
                const num = parseFloat(value);
                return !isNaN(num) && num >= 0.01;
            },
            message: 'Informe um valor monetario valido (minimo R$ 0,01)'
        },
        color: {
            validate: (value) => /^#[0-9A-Fa-f]{6}$/.test(value),
            message: 'Cor invalida (use formato #RRGGBB)'
        }
    };

    // Estilizar campo com erro
    function setError(input, message) {
        const formGroup = input.closest('.mb-3') || input.closest('.col-6') || input.parentElement;
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');

        let feedback = formGroup.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentElement.appendChild(feedback);
        }
        feedback.textContent = message;
    }

    // Limpar erro do campo
    function clearError(input) {
        const formGroup = input.closest('.mb-3') || input.closest('.col-6') || input.parentElement;
        input.classList.remove('is-invalid');

        const value = input.value.trim();
        if (value) {
            input.classList.add('is-valid');
        }

        const feedback = formGroup.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = '';
        }
    }

    // Validar um campo individual
    function validateField(input) {
        const value = input.value;
        let isValid = true;

        // Obter regras do atributo data-validate ou inferir
        let rules = (input.dataset.validate || '').split('|').filter(r => r);

        // Adicionar regras automaticas baseadas em atributos HTML
        if (input.required && !rules.includes('required')) {
            rules.unshift('required');
        }
        if (input.type === 'number' && !rules.includes('numeric')) {
            rules.push('numeric');
        }
        if (input.type === 'date' && !rules.includes('date')) {
            rules.push('date');
        }
        if (input.min && !rules.includes('min')) {
            rules.push(`min:${input.min}`);
        }
        if (input.max && !rules.includes('max')) {
            rules.push(`max:${input.max}`);
        }
        if (input.minLength > 0 && !rules.includes('minLength')) {
            rules.push(`minLength:${input.minLength}`);
        }
        if (input.maxLength > 0 && input.maxLength < 524288 && !rules.includes('maxLength')) {
            rules.push(`maxLength:${input.maxLength}`);
        }

        clearError(input);

        // Se nao for obrigatorio e estiver vazio, pular validacao
        if (!rules.includes('required') && !value.trim()) {
            return true;
        }

        for (const rule of rules) {
            const [ruleName, ruleParam] = rule.split(':');
            const ruleConfig = validationRules[ruleName];

            if (!ruleConfig) continue;

            const valid = ruleConfig.validate(value, ruleParam);
            if (!valid) {
                const message = typeof ruleConfig.message === 'function'
                    ? ruleConfig.message(ruleParam)
                    : ruleConfig.message;
                setError(input, message);
                isValid = false;
                break;
            }
        }

        return isValid;
    }

    // Validar formulario completo
    function validateForm(form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        let isValid = true;
        let firstInvalid = null;

        inputs.forEach(input => {
            if (input.type === 'hidden' || input.disabled) return;

            const fieldValid = validateField(input);
            if (!fieldValid && isValid) {
                firstInvalid = input;
            }
            isValid = isValid && fieldValid;
        });

        // Focar no primeiro campo invalido
        if (firstInvalid) {
            firstInvalid.focus();
        }

        return isValid;
    }

    // Formatar valor monetario em tempo real
    function formatMoney(input) {
        let value = input.value.replace(/\D/g, '');
        if (!value) return;

        value = (parseInt(value) / 100).toFixed(2);
        input.value = value;
    }

    // Inicializar validacao em um formulario
    function initForm(form) {
        if (!form || form.dataset.validationInit) return;
        form.dataset.validationInit = 'true';

        // Validar ao submeter
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        // Validar campos em tempo real
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.type === 'hidden') return;

            // Validar ao sair do campo
            input.addEventListener('blur', function() {
                validateField(this);
            });

            // Limpar erro ao digitar
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });

            // Formatar valores monetarios
            if (input.dataset.format === 'money' || (input.type === 'number' && input.step === '0.01')) {
                input.addEventListener('blur', function() {
                    if (this.value) {
                        this.value = parseFloat(this.value).toFixed(2);
                    }
                });
            }
        });

        // Validacao especifica para data fim vs data inicio
        const dataFim = form.querySelector('[name="data_fim"]');
        const dataInicio = form.querySelector('[name="data_inicio"]');
        if (dataFim && dataInicio) {
            dataFim.addEventListener('change', function() {
                if (dataInicio.value && this.value && new Date(this.value) < new Date(dataInicio.value)) {
                    setError(this, 'Data fim deve ser igual ou posterior a data inicio');
                }
            });
        }
    }

    // Inicializar quando o DOM estiver pronto
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar todos os formularios
        document.querySelectorAll('form').forEach(initForm);

        // Observer para formularios carregados dinamicamente (ex: em modais)
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        if (node.tagName === 'FORM') {
                            initForm(node);
                        }
                        node.querySelectorAll && node.querySelectorAll('form').forEach(initForm);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    // Reinicializar ao abrir modal
    document.addEventListener('shown.bs.modal', function(e) {
        const modal = e.target;
        const forms = modal.querySelectorAll('form');
        forms.forEach(form => {
            // Resetar estado de validacao
            form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
                el.classList.remove('is-invalid', 'is-valid');
            });
        });
    });

    // Expor funcoes globalmente
    window.Validation = {
        validateForm: validateForm,
        validateField: validateField,
        initForm: initForm,
        setError: setError,
        clearError: clearError
    };
})();
