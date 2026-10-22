import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'bootstrap-icons/font/bootstrap-icons.css';

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

const coverInput = document.querySelector('#cover-image');
const coverPreview = document.querySelector('#cover-upload-preview');
const coverPreviewImage = document.querySelector('#cover-preview-image');

coverInput?.addEventListener('change', () => {
    const file = coverInput.files?.[0];

    if (!file || !coverPreviewImage || !coverPreview) {
        return;
    }

    coverPreviewImage.src = URL.createObjectURL(file);
    coverPreview.hidden = false;
});

const adminLayout = document.querySelector('#admin-layout');
const adminSidebar = document.querySelector('#admin-sidebar');
const adminSidebarToggle = document.querySelector('[data-admin-toggle]');
const adminSidebarBackdrop = document.querySelector('[data-admin-backdrop]');
const isMobileAdminView = () => window.matchMedia('(max-width: 760px)').matches;

const adminPanelToggles = [...document.querySelectorAll('[data-admin-panel-toggle]')];
const closeAdminPanels = () => {
    adminPanelToggles.forEach((toggle) => {
        const panel = document.getElementById(toggle.getAttribute('aria-controls'));

        if (!panel) {
            return;
        }

        toggle.setAttribute('aria-expanded', 'false');
        panel.classList.remove('is-visible');
        panel.hidden = true;
    });
};

adminPanelToggles.forEach((toggle) => {
    const panel = document.getElementById(toggle.getAttribute('aria-controls'));

    if (!panel) {
        return;
    }

    toggle.addEventListener('click', () => {
        const shouldOpen = panel.hidden;
        closeAdminPanels();

        if (!shouldOpen) {
            return;
        }

        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        window.requestAnimationFrame(() => panel.classList.add('is-visible'));
    });
});

document.addEventListener('click', (event) => {
    if (!event.target.closest?.('[data-admin-topbar-tool]')) {
        closeAdminPanels();
    }
});

const adminSearchToggle = document.querySelector('[data-admin-search-toggle]');
const adminSearchDialog = document.querySelector('[data-admin-search-dialog]');
const adminSearchInput = document.querySelector('[data-admin-search-input]');
const adminSearchClose = document.querySelector('[data-admin-search-close]');
let adminSearchCloseTimer;

const closeAdminSearch = (restoreFocus = true) => {
    if (!adminSearchDialog || adminSearchDialog.hidden) {
        return;
    }

    window.clearTimeout(adminSearchCloseTimer);
    adminSearchDialog.classList.remove('is-visible');
    adminSearchToggle?.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('admin-search-open');
    adminSearchCloseTimer = window.setTimeout(() => {
        adminSearchDialog.hidden = true;

        if (restoreFocus) {
            adminSearchToggle?.focus();
        }
    }, 180);
};

adminSearchToggle?.addEventListener('click', () => {
    if (!adminSearchDialog) {
        return;
    }

    if (!adminSearchDialog.hidden) {
        closeAdminSearch();
        return;
    }

    window.clearTimeout(adminSearchCloseTimer);
    closeAdminPanels();
    adminSearchDialog.hidden = false;
    adminSearchToggle.setAttribute('aria-expanded', 'true');
    document.body.classList.add('admin-search-open');
    window.requestAnimationFrame(() => {
        adminSearchDialog.classList.add('is-visible');
        adminSearchInput?.focus();
    });
});

adminSearchClose?.addEventListener('click', () => closeAdminSearch());
adminSearchDialog?.addEventListener('click', (event) => {
    if (event.target === adminSearchDialog) {
        closeAdminSearch();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    if (adminSearchDialog && !adminSearchDialog.hidden) {
        closeAdminSearch();
        return;
    }

    closeAdminPanels();
});

const libraryDashboard = document.querySelector('.library-dashboard');
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (libraryDashboard) {
    document.documentElement.classList.add('dashboard-motion-ready');

    const dashboardSections = [...libraryDashboard.querySelectorAll(
        '.library-metric-card, .activity-panel, .recent-loans-panel, .dashboard-quick-panel'
    )];

    dashboardSections.forEach((section, index) => {
        section.style.setProperty('--dashboard-enter-delay', `${Math.min(index * 90, 540)}ms`);

        if (prefersReducedMotion) {
            section.classList.add('is-entered');
            return;
        }

        window.requestAnimationFrame(() => section.classList.add('is-entered'));
    });

    libraryDashboard.querySelectorAll('[data-count-target]').forEach((counter) => {
        const target = Number(counter.dataset.countTarget);

        if (!Number.isFinite(target)) {
            return;
        }

        if (prefersReducedMotion || target === 0) {
            counter.textContent = Math.round(target).toLocaleString('en-US');
            return;
        }

        const duration = 1250;
        const startCount = (startedAt) => {
            const updateCount = (now) => {
                const progress = Math.min((now - startedAt) / duration, 1);
                const easedProgress = 1 - Math.pow(1 - progress, 3);
                counter.textContent = Math.round(target * easedProgress).toLocaleString('en-US');

                if (progress < 1) {
                    window.requestAnimationFrame(updateCount);
                }
            };

            window.requestAnimationFrame(updateCount);
        };

        counter.textContent = '0';
        window.requestAnimationFrame(startCount);
    });

    const chartSvg = libraryDashboard.querySelector('.circulation-chart svg');

    if (chartSvg && !prefersReducedMotion) {
        chartSvg.querySelectorAll('.chart-line').forEach((line) => {
            try {
                line.style.setProperty('--chart-path-length', line.getTotalLength());
            } catch {
                // Leave the line visible if the browser cannot measure its SVG length.
            }
        });

        chartSvg.querySelectorAll('.chart-point').forEach((point, index) => {
            point.style.setProperty('--chart-point-delay', `${850 + Math.floor(index / 2) * 95}ms`);
        });

        window.requestAnimationFrame(() => chartSvg.classList.add('is-drawing'));
    }
}

const activityChart = document.querySelector('.circulation-chart');
const activityTooltip = activityChart?.querySelector('[data-chart-tooltip]');

if (activityChart && activityTooltip) {
    let hideActivityTooltip;

    const showActivityTooltip = (point) => {
        window.clearTimeout(hideActivityTooltip);

        activityTooltip.querySelector('[data-chart-tooltip-date]').textContent = point.dataset.chartDate;
        activityTooltip.querySelector('[data-chart-tooltip-issued]').textContent = point.dataset.chartIssued;
        activityTooltip.querySelector('[data-chart-tooltip-returned]').textContent = point.dataset.chartReturned;

        const wasHidden = activityTooltip.hidden;
        activityTooltip.hidden = false;

        const chartBounds = activityChart.getBoundingClientRect();
        const pointBounds = point.getBoundingClientRect();
        const pointX = pointBounds.left - chartBounds.left + pointBounds.width / 2;
        const pointY = pointBounds.top - chartBounds.top + pointBounds.height / 2;
        const halfTooltipWidth = activityTooltip.offsetWidth / 2;
        const left = Math.min(Math.max(pointX, halfTooltipWidth + 8), chartBounds.width - halfTooltipWidth - 8);
        const showBelow = pointY < activityTooltip.offsetHeight + 18;

        activityTooltip.style.left = `${left}px`;
        activityTooltip.style.top = `${showBelow ? pointY + 8 : pointY - 8}px`;
        activityTooltip.classList.toggle('is-below', showBelow);

        if (wasHidden) {
            window.requestAnimationFrame(() => activityTooltip.classList.add('is-visible'));
        } else {
            activityTooltip.classList.add('is-visible');
        }
    };

    const hideActivityTooltipSoon = (event) => {
        if (event.relatedTarget?.closest?.('.chart-point[data-chart-date]')) {
            return;
        }

        activityTooltip.classList.remove('is-visible');
        hideActivityTooltip = window.setTimeout(() => {
            activityTooltip.hidden = true;
        }, 170);
    };

    activityChart.addEventListener('pointerover', (event) => {
        const point = event.target.closest?.('.chart-point[data-chart-date]');

        if (point) {
            showActivityTooltip(point);
        }
    });

    activityChart.addEventListener('pointerout', hideActivityTooltipSoon);
    activityChart.addEventListener('focusin', (event) => {
        const point = event.target.closest?.('.chart-point[data-chart-date]');

        if (point) {
            showActivityTooltip(point);
        }
    });
    activityChart.addEventListener('focusout', hideActivityTooltipSoon);
}

if (adminLayout && adminSidebar && adminSidebarToggle) {
    if (isMobileAdminView()) {
        adminSidebarToggle.setAttribute('aria-expanded', 'false');
    }

    adminSidebarToggle.addEventListener('click', () => {
        if (isMobileAdminView()) {
            const isOpen = adminLayout.classList.toggle('sidebar-open');
            adminSidebar.classList.toggle('is-open', isOpen);
            adminSidebarToggle.setAttribute('aria-expanded', String(isOpen));
            return;
        }

        const isExpanded = !adminLayout.classList.toggle('sidebar-collapsed');
        adminSidebarToggle.setAttribute('aria-expanded', String(isExpanded));
    });

    adminSidebarBackdrop?.addEventListener('click', () => {
        adminLayout.classList.remove('sidebar-open');
        adminSidebar.classList.remove('is-open');
        adminSidebarToggle.setAttribute('aria-expanded', 'false');
    });

    adminSidebar.querySelectorAll('.admin-nav-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (isMobileAdminView()) {
                adminLayout.classList.remove('sidebar-open');
                adminSidebar.classList.remove('is-open');
                adminSidebarToggle.setAttribute('aria-expanded', 'false');
            }
        });
    });

    window.addEventListener('resize', () => {
        if (!isMobileAdminView()) {
            adminLayout.classList.remove('sidebar-open');
            adminSidebar.classList.remove('is-open');
        }
    });
}
