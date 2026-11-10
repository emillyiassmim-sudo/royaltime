document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.querySelector('[data-nav-toggle]');
    const navMenu = document.querySelector('[data-nav-menu]');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            const isOpen = navMenu.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', String(isOpen));
            navToggle.setAttribute('aria-label', isOpen ? 'Fechar menu' : 'Abrir menu');
        });
    }

    const searchToggle = document.querySelector('[data-search-toggle]');
    const searchPanel = document.querySelector('[data-search-panel]');
    const searchInput = searchPanel?.querySelector('input[type="search"]');

    if (searchToggle && searchPanel) {
        const closeSearch = () => {
            searchPanel.hidden = true;
            searchToggle.setAttribute('aria-expanded', 'false');
        };

        searchToggle.addEventListener('click', () => {
            searchPanel.hidden = !searchPanel.hidden;
            searchToggle.setAttribute('aria-expanded', String(!searchPanel.hidden));

            if (!searchPanel.hidden) {
                searchInput?.focus();
            }
        });

        document.addEventListener('click', (event) => {
            if (!searchPanel.contains(event.target) && !searchToggle.contains(event.target)) {
                closeSearch();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeSearch();
            }
        });
    }

    const gallery = document.querySelector('[data-product-gallery]');

    if (!gallery) {
        return;
    }

    const mainImage = gallery.querySelector('[data-gallery-main]');
    const modal = document.querySelector('[data-product-modal]');
    const modalImage = modal ? modal.querySelector('[data-modal-image]') : null;
    const thumbnails = Array.from(gallery.querySelectorAll('[data-gallery-thumb]'));
    const prevButton = gallery.querySelector('[data-gallery-prev]');
    const nextButton = gallery.querySelector('[data-gallery-next]');
    const shareButton = document.querySelector('[data-share-product]');
    const zoomInButton = modal ? modal.querySelector('[data-zoom-in]') : null;
    const zoomOutButton = modal ? modal.querySelector('[data-zoom-out]') : null;
    const closeButtons = modal ? modal.querySelectorAll('[data-close-modal]') : [];
    const stage = gallery.querySelector('.gallery-stage');

    let currentIndex = 0;
    let zoomValue = 1;
    let touchStartX = 0;
    let pointerStartX = 0;
    let pointerStartY = 0;
    let dragActive = false;

    const setActiveThumbnail = (index) => {
        thumbnails.forEach((thumbnail, thumbnailIndex) => {
            thumbnail.classList.toggle('is-active', thumbnailIndex === index);
        });
    };

    const updateGalleryImage = (index) => {
        if (!thumbnails.length) {
            return;
        }

        currentIndex = (index + thumbnails.length) % thumbnails.length;
        const selectedImage = thumbnails[currentIndex].dataset.image;

        if (selectedImage) {
            mainImage.src = selectedImage;
            mainImage.alt = mainImage.alt.split(' - ')[0] ?? mainImage.alt;
        }

        setActiveThumbnail(currentIndex);

        if (modal && modalImage) {
            modalImage.src = selectedImage;
        }
    };

    const openModal = () => {
        if (!modal || !modalImage) {
            return;
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        modalImage.src = thumbnails[currentIndex]?.dataset.image ?? mainImage.src;
        modalImage.style.transform = 'scale(1)';
        zoomValue = 1;
    };

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    thumbnails.forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', () => updateGalleryImage(index));
    });

    prevButton?.addEventListener('click', () => updateGalleryImage(currentIndex - 1));
    nextButton?.addEventListener('click', () => updateGalleryImage(currentIndex + 1));
    mainImage?.addEventListener('click', openModal);

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    }

    zoomInButton?.addEventListener('click', () => {
        if (!modalImage) {
            return;
        }

        zoomValue = Math.min(3, Number((zoomValue + 0.5).toFixed(2)));
        modalImage.style.transform = `scale(${zoomValue})`;
    });

    zoomOutButton?.addEventListener('click', () => {
        if (!modalImage) {
            return;
        }

        zoomValue = Math.max(1, Number((zoomValue - 0.5).toFixed(2)));
        modalImage.style.transform = `scale(${zoomValue})`;
    });

    modalImage?.addEventListener('pointerdown', (event) => {
        if (zoomValue <= 1) {
            return;
        }

        dragActive = true;
        pointerStartX = event.clientX;
        pointerStartY = event.clientY;
        modalImage.setPointerCapture(event.pointerId);
    });

    modalImage?.addEventListener('pointermove', (event) => {
        if (!dragActive || zoomValue <= 1) {
            return;
        }

        const deltaX = event.clientX - pointerStartX;
        const deltaY = event.clientY - pointerStartY;
        modalImage.style.transform = `translate(${deltaX * 0.9}px, ${deltaY * 0.9}px) scale(${zoomValue})`;
    });

    modalImage?.addEventListener('pointerup', () => {
        dragActive = false;
        pointerStartX = 0;
        pointerStartY = 0;
    });

    stage?.addEventListener('touchstart', (event) => {
        touchStartX = event.changedTouches[0].clientX;
    }, { passive: true });

    stage?.addEventListener('touchend', (event) => {
        const touchDiff = event.changedTouches[0].clientX - touchStartX;

        if (Math.abs(touchDiff) > 50) {
            if (touchDiff < 0) {
                updateGalleryImage(currentIndex + 1);
            } else {
                updateGalleryImage(currentIndex - 1);
            }
        }
    }, { passive: true });

    shareButton?.addEventListener('click', async () => {
        const shareUrl = window.location.href;

        try {
            if (navigator.share) {
                await navigator.share({
                    title: document.title,
                    url: shareUrl,
                });

                return;
            }

            await navigator.clipboard.writeText(shareUrl);
            shareButton.textContent = 'Link copiado';
        } catch (error) {
            shareButton.textContent = 'Compartilhar';
        }
    });
});
