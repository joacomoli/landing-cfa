// Variables globales
let currentStep = 1;
const totalSteps = 3;

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeForm();
    initializeAnimations();
    initializeContactForm();
    initializeCarousel();
    initializeAntiSpam();
    initializeBrandsCarousel();
});

// Navegación móvil
function initializeNavigation() {
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    const navLinks = document.querySelectorAll('.nav-link');

    // Toggle del menú móvil
    navToggle.addEventListener('click', function() {
        navToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    // Cerrar menú al hacer clic en un enlace
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navToggle.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });

    // Smooth scroll para enlaces internos
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            // Solo hacer smooth scroll para enlaces internos (que empiecen con #)
            if (targetId && targetId.startsWith('#')) {
                e.preventDefault();
                const targetSection = document.querySelector(targetId);
                
                if (targetSection) {
                    const headerHeight = document.querySelector('.header').offsetHeight;
                    const targetPosition = targetSection.offsetTop - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
            // Para enlaces externos (como contacto.html), dejar que funcionen normalmente
        });
    });

    // Cambiar header al hacer scroll
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.header');
        if (window.scrollY > 100) {
            header.style.background = 'rgba(255, 255, 255, 0.98)';
            header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
        } else {
            header.style.background = 'rgba(255, 255, 255, 0.95)';
            header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
        }
    });
}

// Formulario de cotización
function initializeForm() {
    const form = document.getElementById('cotizacion-form');
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');
    const submitBtn = document.getElementById('submit-btn');
    const tipoSeguro = document.getElementById('tipo-seguro');

    // Mostrar campos específicos según el tipo de seguro
    tipoSeguro.addEventListener('change', function() {
        const selectedValue = this.value;
        const detallesAutomotor = document.getElementById('detalles-automotor');
        const detallesHogar = document.getElementById('detalles-hogar');

        // Ocultar todos los campos específicos
        detallesAutomotor.style.display = 'none';
        detallesHogar.style.display = 'none';

        // Mostrar campos según la selección
        if (selectedValue === 'automotor') {
            detallesAutomotor.style.display = 'block';
        } else if (selectedValue === 'hogar') {
            detallesHogar.style.display = 'block';
        }
    });

    // Navegación entre pasos
    nextBtn.addEventListener('click', function() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateFormStep();
            }
        }
    });

    prevBtn.addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            updateFormStep();
        }
    });

    // Envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (validateCurrentStep()) {
            submitForm();
        }
    });

    // Validación en tiempo real
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
    });
}

// Inicializar anti‑spam (honeypot y timestamp)
function initializeAntiSpam() {
    const tsInputs = document.querySelectorAll('input[name="fp_timestamp"]');
    const now = Date.now().toString();
    tsInputs.forEach(i => { i.value = now; });
}

// Actualizar paso del formulario
function updateFormStep() {
    const steps = document.querySelectorAll('.form-step');
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');
    const submitBtn = document.getElementById('submit-btn');

    // Ocultar todos los pasos
    steps.forEach(step => {
        step.classList.remove('active');
    });

    // Mostrar paso actual
    const currentStepElement = document.querySelector(`[data-step="${currentStep}"]`);
    if (currentStepElement) {
        currentStepElement.classList.add('active');
    }

    // Actualizar botones
    if (currentStep === 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'inline-flex';
        submitBtn.style.display = 'none';
    } else if (currentStep === totalSteps) {
        prevBtn.style.display = 'inline-flex';
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'inline-flex';
    } else {
        prevBtn.style.display = 'inline-flex';
        nextBtn.style.display = 'inline-flex';
        submitBtn.style.display = 'none';
    }

    // Animación de entrada
    if (currentStepElement) {
        currentStepElement.style.opacity = '0';
        currentStepElement.style.transform = 'translateX(20px)';
        
        setTimeout(() => {
            currentStepElement.style.transition = 'all 0.3s ease';
            currentStepElement.style.opacity = '1';
            currentStepElement.style.transform = 'translateX(0)';
        }, 50);
    }
}

// Validar paso actual
function validateCurrentStep() {
    const currentStepElement = document.querySelector(`[data-step="${currentStep}"]`);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });

    // Validación especial para campos de automotor si está seleccionado
    if (currentStep === 2) {
        const tipoSeguro = document.getElementById('tipo-seguro');
        if (tipoSeguro.value === 'automotor') {
            const marcaVehiculo = document.getElementById('marca-vehiculo');
            const modeloVehiculo = document.getElementById('modelo-vehiculo');
            const anioVehiculo = document.getElementById('anio-vehiculo');
            const tipoPoliza = document.getElementById('tipo-poliza');
            
            if (!marcaVehiculo.value.trim()) {
                showFieldError(marcaVehiculo, 'Por favor ingresá la marca del vehículo');
                isValid = false;
            }
            if (!modeloVehiculo.value.trim()) {
                showFieldError(modeloVehiculo, 'Por favor ingresá el modelo del vehículo');
                isValid = false;
            }
            if (!anioVehiculo.value.trim()) {
                showFieldError(anioVehiculo, 'Por favor ingresá el año del vehículo');
                isValid = false;
            }
            if (!tipoPoliza.value) {
                showFieldError(tipoPoliza, 'Por favor seleccioná el tipo de póliza');
                isValid = false;
            }
        }
    }

    return isValid;
}

// Validar campo individual
function validateField(field) {
    const value = field.value.trim();
    const fieldGroup = field.closest('.form-group');
    let isValid = true;
    let errorMessage = '';

    // Remover mensajes de error anteriores
    const existingError = fieldGroup.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }

    // Validaciones específicas
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Este campo es obligatorio';
    } else if (field.type === 'email' && value && !isValidEmail(value)) {
        isValid = false;
        errorMessage = 'Ingresá un email válido';
    } else if (field.type === 'tel' && value && !isValidPhone(value)) {
        isValid = false;
        errorMessage = 'Ingresá un teléfono válido';
    }

    // Mostrar error si existe
    if (!isValid) {
        field.style.borderColor = '#dc3545';
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = errorMessage;
        fieldGroup.appendChild(errorDiv);
    } else {
        field.style.borderColor = '#28a745';
    }

    return isValid;
}

// Función auxiliar para mostrar errores personalizados
function showFieldError(field, message) {
    const fieldGroup = field.closest('.form-group');
    
    // Remover mensajes de error anteriores
    const existingError = fieldGroup.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Mostrar error
    field.style.borderColor = '#dc3545';
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    fieldGroup.appendChild(errorDiv);
}

// Validar email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Validar teléfono
function isValidPhone(phone) {
    const phoneRegex = /^[\d\s\-\+\(\)]+$/;
    return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 8;
}

// Enviar formulario
function submitForm() {
    const form = document.getElementById('cotizacion-form');
    const submitBtn = document.getElementById('submit-btn');
    const formData = new FormData(form);

    // Mostrar estado de carga
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    submitBtn.disabled = true;
    form.classList.add('loading');

    // Enviar datos a PHP
    fetch('process_cotizacion.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage(data.message);
            
            // Resetear formulario
            form.reset();
            currentStep = 1;
            updateFormStep();
            
            // Scroll al mensaje de éxito
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            showErrorMessage(data.message || 'Hubo un error al enviar tu solicitud. Por favor, intentá nuevamente.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('Hubo un error de conexión. Por favor, intentá nuevamente.');
    })
    .finally(() => {
        // Restaurar botón
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Cotización';
        submitBtn.disabled = false;
        form.classList.remove('loading');
    });
}

// Mostrar mensaje de éxito
function showSuccessMessage(message) {
    const form = document.getElementById('cotizacion-form');
    const existingMessage = form.querySelector('.success-message');
    const existingError = form.querySelector('.error-message');
    
    if (existingMessage) {
        existingMessage.remove();
    }
    if (existingError) {
        existingError.remove();
    }

    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.textContent = message;
    form.appendChild(successDiv);
}

// Mostrar mensaje de error
function showErrorMessage(message) {
    const form = document.getElementById('cotizacion-form');
    const existingMessage = form.querySelector('.success-message');
    const existingError = form.querySelector('.error-message');
    
    if (existingMessage) {
        existingMessage.remove();
    }
    if (existingError) {
        existingError.remove();
    }

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    form.appendChild(errorDiv);
}

// Formulario de contacto
function initializeContactForm() {
    const contactForm = document.getElementById('contacto-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            const formData = new FormData(this);
            
            // Mostrar estado de carga
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            submitBtn.disabled = true;
            
            // Enviar datos a PHP
            fetch('contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showContactSuccessMessage(data.message);
                    this.reset();
                } else {
                    showContactErrorMessage(data.message || 'Hubo un error al enviar tu mensaje. Por favor, intentá nuevamente.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showContactErrorMessage('Hubo un error de conexión. Por favor, intentá nuevamente.');
            })
            .finally(() => {
                // Restaurar botón
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
}

// Mostrar mensaje de éxito en contacto
function showContactSuccessMessage(message) {
    const contactForm = document.getElementById('contacto-form');
    const existingMessage = contactForm.querySelector('.success-message');
    const existingError = contactForm.querySelector('.error-message');
    
    if (existingMessage) {
        existingMessage.remove();
    }
    if (existingError) {
        existingError.remove();
    }

    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.textContent = message;
    contactForm.appendChild(successDiv);
}

// Mostrar mensaje de error en contacto
function showContactErrorMessage(message) {
    const contactForm = document.getElementById('contacto-form');
    const existingMessage = contactForm.querySelector('.success-message');
    const existingError = contactForm.querySelector('.error-message');
    
    if (existingMessage) {
        existingMessage.remove();
    }
    if (existingError) {
        existingError.remove();
    }

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    contactForm.appendChild(errorDiv);
}

// Animaciones al hacer scroll
function initializeAnimations() {
    // Intersection Observer para animaciones
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);

    // Observar elementos para animar
    const elementsToAnimate = document.querySelectorAll(`
        .servicio-card,
        .beneficio-item,
        .stat-item,
        .contact-method,
        .section-header
    `);

    elementsToAnimate.forEach(element => {
        observer.observe(element);
    });

    // Animación de contadores
    const statNumbers = document.querySelectorAll('.stat-number');
    const statsObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    statNumbers.forEach(stat => {
        statsObserver.observe(stat);
    });
}

// Animación de contadores
function animateCounter(element) {
    const target = element.textContent;
    const isNumber = !isNaN(parseInt(target));
    
    if (isNumber) {
        const finalNumber = parseInt(target);
        let currentNumber = 0;
        const increment = finalNumber / 50;
        const timer = setInterval(() => {
            currentNumber += increment;
            if (currentNumber >= finalNumber) {
                element.textContent = finalNumber + '+';
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(currentNumber) + '+';
            }
        }, 30);
    } else {
        // Para elementos como "24/7"
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.5s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 100);
    }
}

// Efectos adicionales
document.addEventListener('DOMContentLoaded', function() {
    // Efecto parallax suave en el hero
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        if (hero) {
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });

    // Efecto hover en las tarjetas de servicios
    const serviceCards = document.querySelectorAll('.servicio-card');
    serviceCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Efecto de escritura en el título principal
    const heroTitle = document.querySelector('.hero-title');
    if (heroTitle) {
        const text = heroTitle.textContent;
        heroTitle.textContent = '';
        let i = 0;
        
        const typeWriter = () => {
            if (i < text.length) {
                heroTitle.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            }
        };
        
        // Iniciar efecto después de un pequeño delay
        setTimeout(typeWriter, 500);
    }

    // Smooth scroll para botones del hero
    const heroButtons = document.querySelectorAll('.hero .btn');
    heroButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const headerHeight = document.querySelector('.header').offsetHeight;
                    const targetPosition = target.offsetTop - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Validación en tiempo real para el formulario de contacto
    const contactInputs = document.querySelectorAll('#contacto-form input, #contacto-form textarea');
    contactInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateContactField(this);
        });
    });
});

// Validar campos del formulario de contacto
function validateContactField(field) {
    const value = field.value.trim();
    const fieldGroup = field.closest('.form-group');
    let isValid = true;
    let errorMessage = '';

    // Remover mensajes de error anteriores
    const existingError = fieldGroup.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }

    // Validaciones específicas
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Este campo es obligatorio';
    } else if (field.type === 'email' && value && !isValidEmail(value)) {
        isValid = false;
        errorMessage = 'Ingresá un email válido';
    }

    // Mostrar error si existe
    if (!isValid) {
        field.style.borderColor = '#dc3545';
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = errorMessage;
        fieldGroup.appendChild(errorDiv);
    } else {
        field.style.borderColor = '#28a745';
    }

    return isValid;
}

// Función para mostrar notificaciones toast
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Estilos del toast
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '15px 20px',
        borderRadius: '8px',
        color: 'white',
        fontWeight: '500',
        zIndex: '10000',
        transform: 'translateX(100%)',
        transition: 'transform 0.3s ease',
        maxWidth: '300px',
        backgroundColor: type === 'success' ? '#28a745' : '#dc3545'
    });
    
    document.body.appendChild(toast);
    
    // Animar entrada
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Función para formatear números de teléfono
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 0) {
        if (value.length <= 2) {
            value = value;
        } else if (value.length <= 4) {
            value = value.slice(0, 2) + ' ' + value.slice(2);
        } else if (value.length <= 8) {
            value = value.slice(0, 2) + ' ' + value.slice(2, 4) + ' ' + value.slice(4);
        } else {
            value = value.slice(0, 2) + ' ' + value.slice(2, 4) + ' ' + value.slice(4, 8) + ' ' + value.slice(8, 12);
        }
    }
    
    input.value = value;
}

// Aplicar formateo a campos de teléfono
document.addEventListener('DOMContentLoaded', function() {
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            formatPhoneNumber(this);
        });
    });
});

// Función para detectar si el usuario está en móvil
function isMobile() {
    return window.innerWidth <= 768;
}

// Optimización para dispositivos móviles
if (isMobile()) {
    // Reducir animaciones en móviles para mejor rendimiento
    document.documentElement.style.setProperty('--transition', 'all 0.2s ease');
    
    // Desactivar efectos parallax en móviles
    window.removeEventListener('scroll', function() {
        const hero = document.querySelector('.hero');
        if (hero) {
            hero.style.transform = 'none';
        }
    });
}

// Función para lazy loading de imágenes (si se agregan imágenes en el futuro)
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Inicializar lazy loading si hay imágenes
document.addEventListener('DOMContentLoaded', lazyLoadImages);

// Carrusel de marcas con funcionalidad de arrastre
function initializeBrandsCarousel() {
    const brandsTrack = document.querySelector('.brands-track');
    const brandsWrapper = document.querySelector('.brands-wrapper');
    
    if (!brandsTrack || !brandsWrapper) {
        return;
    }
    
    let isDragging = false;
    let startX = 0;
    let currentTranslate = 0;
    let animationId = null;
    let autoScrollSpeed = 1; // px por frame
    let direction = -1; // -1 para izquierda, 1 para derecha
    let isPaused = false;
    
    // Iniciar scroll automático
    function startAutoScroll() {
        if (isPaused) return;
        
        function animate() {
            if (!isDragging && !isPaused) {
                currentTranslate += autoScrollSpeed * direction;
                
                // Reset cuando llega al final
                const trackWidth = brandsTrack.scrollWidth / 2; // Dividido por 2 porque duplicamos los elementos
                if (Math.abs(currentTranslate) >= trackWidth) {
                    currentTranslate = 0;
                }
                
                brandsTrack.style.transform = `translateX(${currentTranslate}px)`;
            }
            animationId = requestAnimationFrame(animate);
        }
        animate();
    }
    
    // Pausar scroll automático
    function pauseAutoScroll() {
        isPaused = true;
        if (animationId) {
            cancelAnimationFrame(animationId);
        }
    }
    
    // Reanudar scroll automático
    function resumeAutoScroll() {
        isPaused = false;
        startAutoScroll();
    }
    
    // Eventos de mouse
    brandsTrack.addEventListener('mousedown', (e) => {
        isDragging = true;
        startX = e.clientX;
        brandsTrack.classList.add('dragging');
        pauseAutoScroll();
        e.preventDefault();
    });
    
    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        
        const deltaX = e.clientX - startX;
        const newTranslate = currentTranslate + deltaX;
        brandsTrack.style.transform = `translateX(${newTranslate}px)`;
    });
    
    document.addEventListener('mouseup', () => {
        if (isDragging) {
            isDragging = false;
            brandsTrack.classList.remove('dragging');
            resumeAutoScroll();
        }
    });
    
    // Eventos táctiles para móvil
    brandsTrack.addEventListener('touchstart', (e) => {
        isDragging = true;
        startX = e.touches[0].clientX;
        brandsTrack.classList.add('dragging');
        pauseAutoScroll();
    });
    
    brandsTrack.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        
        const deltaX = e.touches[0].clientX - startX;
        const newTranslate = currentTranslate + deltaX;
        brandsTrack.style.transform = `translateX(${newTranslate}px)`;
        e.preventDefault();
    });
    
    brandsTrack.addEventListener('touchend', () => {
        if (isDragging) {
            isDragging = false;
            brandsTrack.classList.remove('dragging');
            resumeAutoScroll();
        }
    });
    
    // Pausar al hacer hover
    brandsWrapper.addEventListener('mouseenter', pauseAutoScroll);
    brandsWrapper.addEventListener('mouseleave', resumeAutoScroll);
    
    // Pausar cuando la página no está visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            pauseAutoScroll();
        } else {
            resumeAutoScroll();
        }
    });
    
    // Iniciar animación
    startAutoScroll();
}

// Carousel de servicios
function initializeCarousel() {
    const carouselTrack = document.getElementById('carousel-track');
    const carouselProgress = document.getElementById('carousel-progress');
    const prevBtn = document.getElementById('carousel-prev');
    const nextBtn = document.getElementById('carousel-next');
    const dots = document.querySelectorAll('.dot');
    
    if (!carouselTrack || !prevBtn || !nextBtn) {
        return;
    }
    
    let currentSlide = 0;
    const totalSlides = 12;
    
    function updateCarousel() {
        const translateX = -currentSlide * 100;
        carouselTrack.style.transform = `translateX(${translateX}%)`;
        
        // Actualizar indicador de progreso
        if (carouselProgress) {
            const progressWidth = ((currentSlide + 1) / totalSlides) * 100;
            carouselProgress.style.width = `${progressWidth}%`;
        }
        
        // Actualizar dots
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });
        
        // Actualizar botones
        prevBtn.disabled = currentSlide === 0;
        nextBtn.disabled = currentSlide === totalSlides - 1;
        
        // Agregar efecto de "pulse" al slide activo
        const slides = document.querySelectorAll('.carousel-slide');
        slides.forEach((slide, index) => {
            slide.classList.toggle('active', index === currentSlide);
        });
    }
    
    function nextSlide() {
        if (currentSlide < totalSlides - 1) {
            currentSlide++;
            updateCarousel();
        }
    }
    
    function prevSlide() {
        if (currentSlide > 0) {
            currentSlide--;
            updateCarousel();
        }
    }
    
    function goToSlide(slideIndex) {
        currentSlide = slideIndex;
        updateCarousel();
    }
    
    // Event listeners
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => goToSlide(index));
    });
    
    // Auto-play mejorado
    let autoPlayInterval;
    let autoPlayPaused = false;
    
    function startAutoPlay() {
        if (autoPlayPaused) return;
        
        autoPlayInterval = setInterval(() => {
            if (currentSlide < totalSlides - 1) {
                nextSlide();
            } else {
                currentSlide = 0;
                updateCarousel();
            }
        }, 6000); // Cambia cada 6 segundos
    }
    
    function stopAutoPlay() {
        clearInterval(autoPlayInterval);
    }
    
    function pauseAutoPlay() {
        autoPlayPaused = true;
        stopAutoPlay();
    }
    
    function resumeAutoPlay() {
        autoPlayPaused = false;
        startAutoPlay();
    }
    
    // Pausar auto-play al interactuar
    const carouselContainer = document.querySelector('.servicios-carousel');
    if (carouselContainer) {
        carouselContainer.addEventListener('mouseenter', pauseAutoPlay);
        carouselContainer.addEventListener('mouseleave', resumeAutoPlay);
        carouselContainer.addEventListener('touchstart', pauseAutoPlay);
        
        // Reanudar auto-play después de 3 segundos de inactividad
        let inactivityTimer;
        carouselContainer.addEventListener('touchend', () => {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                resumeAutoPlay();
            }, 3000);
        });
    }
    
    // Pausar auto-play cuando la página no está visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            pauseAutoPlay();
        } else {
            resumeAutoPlay();
        }
    });
    
    // Inicializar
    updateCarousel();
    startAutoPlay();
    
    // Swipe mejorado para móvil
    let startX = 0;
    let startY = 0;
    let endX = 0;
    let endY = 0;
    let isDragging = false;
    
    carouselTrack.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        isDragging = true;
        carouselTrack.style.transition = 'none';
    });
    
    carouselTrack.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        
        const currentX = e.touches[0].clientX;
        const currentY = e.touches[0].clientY;
        const diffX = currentX - startX;
        const diffY = currentY - startY;
        
        // Solo procesar si es un swipe horizontal
        if (Math.abs(diffX) > Math.abs(diffY)) {
            e.preventDefault();
            const translateX = -currentSlide * 100 + (diffX / carouselTrack.offsetWidth) * 100;
            carouselTrack.style.transform = `translateX(${translateX}%)`;
        }
    });
    
    carouselTrack.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        
        endX = e.changedTouches[0].clientX;
        endY = e.changedTouches[0].clientY;
        
        carouselTrack.style.transition = 'transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        isDragging = false;
        
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeThreshold = 80;
        const diffX = startX - endX;
        const diffY = Math.abs(startY - endY);
        
        // Solo procesar si es un swipe horizontal significativo
        if (Math.abs(diffX) > swipeThreshold && Math.abs(diffX) > diffY) {
            if (diffX > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        } else {
            // Volver al slide actual si el swipe no fue suficiente
            updateCarousel();
        }
    }
    
    // Agregar feedback visual durante el swipe
    carouselTrack.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        
        const currentX = e.touches[0].clientX;
        const diffX = currentX - startX;
        
        if (Math.abs(diffX) > 50) {
            carouselTrack.style.opacity = '0.8';
        }
    });
    
    carouselTrack.addEventListener('touchend', () => {
        carouselTrack.style.opacity = '1';
    });
}

