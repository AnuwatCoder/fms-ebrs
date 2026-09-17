/* Liner Theme Manager — dark mode, RTL, color preset, sidebar, horizontal layout
   Persists to localStorage. Auto-applies on every page (run before body renders to avoid FOUC). */
(function () {
  const KEY = {
    dark:   'liner-dark',
    rtl:    'liner-rtl',
    sb:     'liner-sidebar-collapsed',
    horiz:  'liner-horizontal',
    color:  'liner-color-preset',
  };

  const Theme = {
    get isDark()    { return localStorage.getItem(KEY.dark)  === 'true'; },
    get isRTL()     { return localStorage.getItem(KEY.rtl)   === 'true'; },
    get collapsed() { return localStorage.getItem(KEY.sb)    === 'true'; },
    get horizontal(){ return localStorage.getItem(KEY.horiz) === 'true'; },
    get color()     { return localStorage.getItem(KEY.color) || 'navy'; },

    setDark(v) {
      localStorage.setItem(KEY.dark, !!v);
      document.documentElement.classList.toggle('dark', !!v);
      window.dispatchEvent(new CustomEvent('liner:dark', { detail: !!v }));
    },
    setRTL(v) {
      localStorage.setItem(KEY.rtl, !!v);
      document.documentElement.dir = v ? 'rtl' : 'ltr';
    },
    setCollapsed(v) {
      localStorage.setItem(KEY.sb, !!v);
      document.body.classList.toggle('sidebar-collapsed', !!v);
      const sb = document.querySelector('.app-sidebar');
      if (sb) sb.classList.toggle('collapsed', !!v);
    },
    setHorizontal(v) {
      localStorage.setItem(KEY.horiz, !!v);
      document.body.classList.toggle('horizontal-mode', !!v);
      // when switching, reload page so sidebar/horizontal markup is correct
      window.dispatchEvent(new CustomEvent('liner:horizontal', { detail: !!v }));
    },
    setColor(v) {
      localStorage.setItem(KEY.color, v);
      document.documentElement.setAttribute('data-theme', v);
    },

    toggleDark()       { this.setDark(!this.isDark); },
    toggleRTL()        { this.setRTL(!this.isRTL); },
    toggleCollapsed()  { this.setCollapsed(!this.collapsed); },
    toggleHorizontal() { this.setHorizontal(!this.horizontal); },
  };

  // Apply early (called from <head>)
  Theme.apply = function () {
    document.documentElement.classList.toggle('dark', Theme.isDark);
    document.documentElement.dir = Theme.isRTL ? 'rtl' : 'ltr';
    document.documentElement.setAttribute('data-theme', Theme.color);
    // body classes may not exist yet — defer if needed
    const tryApplyBody = () => {
      if (document.body) {
        document.body.classList.toggle('sidebar-collapsed', Theme.collapsed);
        document.body.classList.toggle('horizontal-mode', Theme.horizontal);
      } else {
        requestAnimationFrame(tryApplyBody);
      }
    };
    tryApplyBody();
  };
  Theme.apply();

  window.LinerTheme = Theme;
})();

/* Menu structure — mirrors React menuItems.js exactly */
window.LINER_MENU = [
  {
    group: 'Dashboards', groupIcon: 'layout-dashboard',
    items: [
      { label: 'Modern',    icon: 'layout-dashboard', path: 'dashboard.html' },
      { label: 'Analytics', icon: 'activity',         path: 'analytics.html' },
      { label: 'eCommerce', icon: 'shopping-cart',    path: 'ecommerce.html' },
      { label: 'Music',     icon: 'music',            path: 'music.html' },
      { label: 'General',   icon: 'globe-2',          path: 'general.html' },
    ]
  },
  {
    group: 'Apps', groupIcon: 'layers',
    items: [
      { label: 'Calendar',     icon: 'calendar',        path: 'calendar.html' },
      { label: 'Kanban',       icon: 'kanban-square',   path: 'kanban.html' },
      { label: 'Chat',         icon: 'message-square',  path: 'chat.html' },
      { label: 'Email',        icon: 'mail',            path: 'email.html' },
      { label: 'Notes',        icon: 'sticky-note',     path: 'notes.html' },
      { label: 'Contacts',     icon: 'users',           path: 'contacts.html' },
      { label: 'Tickets',      icon: 'ticket',          path: 'tickets.html' },
      { label: 'Invoice',      icon: 'file-text',       path: 'invoice-list.html' },
      { label: 'User Profile', icon: 'user-circle',     path: 'user-profile.html' },
      { label: 'Blog', icon: 'book-open', children: [
        { label: 'Blog Posts',  icon: 'book-open', path: 'blog-posts.html' },
        { label: 'Blog Post',   icon: 'file-text', path: 'blog-post.html' },
        { label: 'Blog Detail', icon: 'eye',       path: 'blog-detail.html' },
      ]},
      { label: 'eCommerce', icon: 'shopping-bag', children: [
        { label: 'Shop',     icon: 'store',       path: 'ecommerce-shop.html' },
        { label: 'Detail',   icon: 'package',     path: 'ecommerce-detail.html' },
        { label: 'List',     icon: 'list',        path: 'ecommerce-list.html' },
        { label: 'Checkout', icon: 'credit-card', path: 'ecommerce-checkout.html' },
      ]},
    ]
  },
  {
    group: 'UI Elements', groupIcon: 'star',
    items: [
      { label: 'Alert',      icon: 'alert-circle',   path: 'alert.html' },
      { label: 'Accordion',  icon: 'layers',         path: 'accordion.html' },
      { label: 'Avatar',     icon: 'user-circle',    path: 'avatar.html' },
      { label: 'Chip',       icon: 'tag',            path: 'chip.html' },
      { label: 'Dialog',     icon: 'message-square', path: 'dialog.html' },
      { label: 'List',       icon: 'align-left',     path: 'list.html' },
      { label: 'Popover',    icon: 'message-circle', path: 'popover.html' },
      { label: 'Rating',     icon: 'star',           path: 'rating.html' },
      { label: 'Tabs',       icon: 'layout-grid',    path: 'tabs.html' },
      { label: 'Tooltip',    icon: 'info',           path: 'tooltip.html' },
      { label: 'Typography', icon: 'type',           path: 'typography.html' },
      { label: 'Buttons',    icon: 'zap',            path: 'buttons.html' },
      { label: 'Cards',      icon: 'square-stack',   path: 'cards.html' },
    ]
  },
  {
    group: 'Forms', groupIcon: 'square-pen',
    items: [
      { label: 'Form Elements',   icon: 'square-pen',      path: 'form-elements.html' },
      { label: 'Form Layouts',    icon: 'layout-template', path: 'form-layouts.html' },
      { label: 'Form Horizontal', icon: 'rows',            path: 'form-horizontal.html' },
      { label: 'Form Validation', icon: 'check-circle',    path: 'form-validation.html' },
    ]
  },
  {
    group: 'Tables', groupIcon: 'table-2',
    items: [
      { label: 'Basic Table',    icon: 'table-2',          path: 'basic-table.html' },
      { label: 'Enhanced Table', icon: 'table-properties', path: 'enhanced-table.html' },
    ]
  },
  {
    group: 'Charts', groupIcon: 'line-chart',
    items: [
      { label: 'Line Chart',      icon: 'line-chart',  path: 'line-chart.html' },
      { label: 'Area Chart',      icon: 'area-chart',  path: 'area-chart.html' },
      { label: 'Bar Chart',       icon: 'bar-chart-3', path: 'bar-chart.html' },
      { label: 'Column Chart',    icon: 'bar-chart-2', path: 'column-chart.html' },
      { label: 'Donut Chart',     icon: 'pie-chart',   path: 'donut-chart.html' },
      { label: 'Radialbar Chart', icon: 'activity',    path: 'radialbar-chart.html' },
    ]
  },
  {
    group: 'Pages', groupIcon: 'file-text',
    items: [
      { label: 'Account Settings', icon: 'settings',    path: 'account.html' },
      { label: 'Pricing',          icon: 'credit-card', path: 'pricing.html' },
      { label: 'FAQ',              icon: 'help-circle', path: 'faq.html' },
      { label: 'Widgets',          icon: 'layout-grid', path: 'widgets.html' },
      { label: 'API Keys',         icon: 'key',         path: 'apikeys.html' },
    ]
  },
  {
    group: 'Icons', groupIcon: 'smile',
    items: [
      { label: 'Lucide Icons', icon: 'smile', path: 'lucide-icons.html' },
      { label: 'Custom Icons', icon: 'star',  path: 'custom-icons.html' },
    ]
  },
  {
    group: 'Auth', groupIcon: 'shield',
    items: [
      { label: 'Login',           icon: 'log-in',       path: 'login.html' },
      { label: 'Register',        icon: 'user-plus',    path: 'register.html' },
      { label: 'Forgot Password', icon: 'key-round',    path: 'forgot-password.html' },
      { label: 'Two Step',        icon: 'shield',       path: 'two-steps.html' },
      { label: 'Error',           icon: 'alert-circle', path: 'error.html' },
      { label: 'Maintenance',     icon: 'wrench',       path: 'maintenance.html' },
    ]
  },
];

/* Chart color helpers — pulls primary preset from CSS vars */
window.LinerChartColors = function () {
  const style = getComputedStyle(document.documentElement);
  const p = style.getPropertyValue('--p-500').trim().split(/\s+/).map(Number);
  const isDark = document.documentElement.classList.contains('dark');
  return {
    primary: `rgb(${p.join(',')})`,
    primaryAlpha: (a) => `rgb(${p.join(',')} / ${a})`,
    cyan: '#06b6d4',
    emerald: '#10b981',
    amber: '#f59e0b',
    rose: '#f43f5e',
    violet: '#8b5cf6',
    grid: isDark ? '#334155' : '#e2e8f0',
    axis: isDark ? '#94a3b8' : '#64748b',
    text: isDark ? '#cbd5e1' : '#475569',
    bg: isDark ? '#1e293b' : '#ffffff',
  };
};

/* Apex defaults applied to every chart */
window.LinerApexBase = function () {
  const c = window.LinerChartColors();
  return {
    chart: {
      fontFamily: 'Inter, sans-serif',
      toolbar: { show: false },
      animations: { enabled: true, speed: 600 },
      foreColor: c.text,
    },
    grid: { borderColor: c.grid, strokeDashArray: 3, padding: { left: 10, right: 10 } },
    tooltip: { theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
    legend: { labels: { colors: c.text } },
    xaxis: { axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { colors: c.axis, fontSize: '11px' } } },
    yaxis: { labels: { style: { colors: c.axis, fontSize: '11px' } } },
  };
};

/* Layout renderer — flat root paths, no inline styles */
(function () {
  const ICON = (name, cls = '') => `<i data-lucide="${name}" class="${cls}"></i>`;

  function getCurrentSlug() {
    const p = window.location.pathname.replace(/\\/g, '/');
    const file = p.substring(p.lastIndexOf('/') + 1) || 'dashboard.html';
    if (file === 'index.html' || file === '') return 'dashboard.html';
    return file;
  }


  const BREADCRUMBS = {
    'dashboard.html': ['Dashboard', 'Modern'],
    'ecommerce.html': ['Dashboard', 'eCommerce'],
    'music.html':     ['Dashboard', 'Music'],
    'general.html':   ['Dashboard', 'General'],
    'analytics.html': ['Dashboard', 'Analytics'],
    'calendar.html': ['Apps', 'Calendar'],
    'chat.html':     ['Apps', 'Chat'],
    'email.html':    ['Apps', 'Email'],
    'notes.html':    ['Apps', 'Notes'],
    'contacts.html': ['Apps', 'Contacts'],
    'tickets.html':  ['Apps', 'Tickets'],
    'kanban.html':   ['Apps', 'Kanban'],
    'invoice-list.html': ['Apps', 'Invoice'],
    'user-profile.html': ['Apps', 'User Profile'],
    'blog-posts.html': ['Apps', 'Blog Posts'],
    'blog-post.html':  ['Apps', 'Blog Post'],
    'blog-detail.html':['Apps', 'Blog Detail'],
    'ecommerce-shop.html':     ['Apps', 'Shop'],
    'ecommerce-detail.html':   ['Apps', 'Product Detail'],
    'ecommerce-list.html':     ['Apps', 'Product List'],
    'ecommerce-checkout.html': ['Apps', 'Checkout'],
    'buttons.html':   ['UI', 'Buttons'],
    'alert.html':     ['UI', 'Alert'],
    'accordion.html': ['UI', 'Accordion'],
    'avatar.html':    ['UI', 'Avatar'],
    'chip.html':      ['UI', 'Chip'],
    'dialog.html':    ['UI', 'Dialog'],
    'list.html':      ['UI', 'List'],
    'popover.html':   ['UI', 'Popover'],
    'rating.html':    ['UI', 'Rating'],
    'tabs.html':      ['UI', 'Tabs'],
    'tooltip.html':   ['UI', 'Tooltip'],
    'typography.html':['UI', 'Typography'],
    'cards.html':     ['UI', 'Cards'],
    'form-elements.html':   ['Forms', 'Elements'],
    'form-layouts.html':    ['Forms', 'Layouts'],
    'form-horizontal.html': ['Forms', 'Horizontal'],
    'form-validation.html': ['Forms', 'Validation'],
    'basic-table.html':     ['Tables', 'Basic'],
    'enhanced-table.html':  ['Tables', 'Enhanced'],
    'line-chart.html':      ['Charts', 'Line'],
    'area-chart.html':      ['Charts', 'Area'],
    'bar-chart.html':       ['Charts', 'Bar'],
    'column-chart.html':    ['Charts', 'Column'],
    'donut-chart.html':     ['Charts', 'Donut'],
    'radialbar-chart.html': ['Charts', 'Radial'],
    'widgets.html':  ['Pages', 'Widgets'],
    'account.html':  ['Pages', 'Account'],
    'pricing.html':  ['Pages', 'Pricing'],
    'faq.html':      ['Pages', 'FAQ'],
    'apikeys.html':  ['Pages', 'API Keys'],
    'lucide-icons.html': ['Icons', 'Lucide'],
    'custom-icons.html': ['Icons', 'Custom'],
  };


  function bindEvents() {
    document.querySelectorAll('[data-toggle-sidebar]').forEach(b => b.addEventListener('click', () => LinerTheme.toggleCollapsed()));
    document.querySelectorAll('[data-toggle-mobile-sidebar]').forEach(b => b.addEventListener('click', () => {
      document.querySelector('.app-sidebar').classList.toggle('mobile-open');
      document.querySelector('.sidebar-backdrop').classList.toggle('open');
    }));
    document.querySelector('.sidebar-backdrop')?.addEventListener('click', () => {
      document.querySelector('.app-sidebar').classList.remove('mobile-open');
      document.querySelector('.sidebar-backdrop').classList.remove('open');
    });
    document.querySelectorAll('[data-toggle-dark]').forEach(b => b.addEventListener('click', () => {
      LinerTheme.toggleDark();
      b.querySelector('i')?.setAttribute('data-lucide', LinerTheme.isDark ? 'sun' : 'moon');
      window.lucide && lucide.createIcons();
    }));
    document.querySelectorAll('[data-toggle-settings]').forEach(b => b.addEventListener('click', () => {
      document.querySelector('.settings-panel').classList.add('open');
      document.querySelector('.settings-overlay').classList.add('open');
    }));
    document.querySelectorAll('[data-close-settings]').forEach(b => b.addEventListener('click', () => {
      document.querySelector('.settings-panel').classList.remove('open');
      document.querySelector('.settings-overlay').classList.remove('open');
    }));
    document.querySelectorAll('[data-mode]').forEach(b => b.addEventListener('click', () => {
      const m = b.getAttribute('data-mode');
      LinerTheme.setDark(m === 'dark');
      document.querySelectorAll('[data-mode]').forEach(x => x.classList.toggle('active', x.getAttribute('data-mode') === m));
      document.querySelectorAll('[data-toggle-dark] i').forEach(i => i.setAttribute('data-lucide', LinerTheme.isDark ? 'sun' : 'moon'));
      window.lucide && lucide.createIcons();
    }));
    document.querySelectorAll('[data-color]').forEach(b => b.addEventListener('click', () => {
      const c = b.getAttribute('data-color');
      LinerTheme.setColor(c);
      document.querySelectorAll('[data-color]').forEach(x => {
        x.classList.remove('active','outline-navy','outline-blue','outline-green','outline-orange','outline-red');
        if (x.getAttribute('data-color') === c) x.classList.add('active','outline-' + c);
      });
    }));
    document.querySelectorAll('[data-setting]').forEach(s => s.addEventListener('click', () => {
      const key = s.getAttribute('data-setting');
      if (key === 'collapsed') { LinerTheme.toggleCollapsed(); s.classList.toggle('on', LinerTheme.collapsed); }
      else if (key === 'horizontal') { LinerTheme.toggleHorizontal(); s.classList.toggle('on', LinerTheme.horizontal); location.reload(); }
      else if (key === 'rtl') { LinerTheme.toggleRTL(); s.classList.toggle('on', LinerTheme.isRTL); }
    }));
    document.querySelectorAll('[data-toggle-submenu]').forEach(a => a.addEventListener('click', (e) => {
      // When collapsed, click on the parent icon should navigate to the first child
      if (document.body.classList.contains('sidebar-collapsed')) {
        const first = a.nextElementSibling?.querySelector('a[data-path]');
        if (first) { window.location.href = first.getAttribute('href'); return; }
      }
      e.preventDefault();
      const submenu = a.nextElementSibling;
      submenu?.classList.toggle('open');
      a.classList.toggle('expanded');
    }));

    /* Build collapsed sidebar — one icon-per-group with flyout dropdown */
    (function initCollapsedGroups() {
      const sidebar = document.querySelector('.app-sidebar');
      const nav = document.querySelector('.app-sidebar .sidebar-nav');
      if (!sidebar || !nav || !window.LINER_MENU) return;

      // Build collapsed-nav markup from window.LINER_MENU
      const current = (window.location.pathname.split('/').pop() || 'dashboard.html');
      const collapsedNav = document.createElement('nav');
      collapsedNav.className = 'sidebar-nav-collapsed custom-scrollbar';
      collapsedNav.innerHTML = window.LINER_MENU.map((g, gi) => {
        const allPaths = [];
        g.items.forEach(it => {
          if (it.children) it.children.forEach(c => allPaths.push(c.path));
          else if (it.path) allPaths.push(it.path);
        });
        const isActive = allPaths.includes(current);
        // Flatten items for flyout: nested children get a header above
        const flatHtml = g.items.map(it => {
          if (it.children) {
            return `<div class="sidebar-flyout-header">${it.label}</div>` +
              it.children.map(c => `<a href="${c.path}" class="sidebar-flyout-link${c.path === current ? ' active' : ''}"><i data-lucide="${c.icon}" class="lucide-sm"></i><span>${c.label}</span></a>`).join('');
          }
          return `<a href="${it.path}" class="sidebar-flyout-link${it.path === current ? ' active' : ''}"><i data-lucide="${it.icon}" class="lucide-sm"></i><span>${it.label}</span></a>`;
        }).join('');
        return `<div class="sidebar-coll-group" data-group="${gi}">
          <button class="sidebar-coll-trigger${isActive ? ' active' : ''}" type="button">
            <i data-lucide="${g.groupIcon}" class="lucide-md"></i>
            <span class="sidebar-coll-label">${g.group}</span>
          </button>
          <div class="sidebar-flyout" data-flyout>
            <div class="sidebar-flyout-title">${g.group}</div>
            ${flatHtml}
          </div>
        </div>`;
      }).join('');

      // Insert right after the regular nav
      nav.parentNode.insertBefore(collapsedNav, nav.nextSibling);
      window.lucide && lucide.createIcons();

      // Flyout positioning + hover handlers
      let activeFlyout = null;
      let hideTimer = null;
      function hide() { if (activeFlyout) { activeFlyout.classList.remove('sidebar-flyout-open'); activeFlyout.style.cssText = ''; activeFlyout = null; } }
      function show(trigger, flyout) {
        if (activeFlyout && activeFlyout !== flyout) hide();
        clearTimeout(hideTimer);
        const rect = trigger.getBoundingClientRect();
        flyout.classList.add('sidebar-flyout-open');
        const isRtl = document.documentElement.dir === 'rtl';
        const inSidebar = sidebar.getBoundingClientRect();
        Object.assign(flyout.style, {
          position: 'fixed',
          top: rect.top + 'px',
          zIndex: '9999',
          maxHeight: (window.innerHeight - rect.top - 16) + 'px',
          [isRtl ? 'right' : 'left']: (isRtl ? (window.innerWidth - inSidebar.left + 6) : (inSidebar.right + 6)) + 'px',
        });
        activeFlyout = flyout;
      }
      collapsedNav.querySelectorAll('.sidebar-coll-group').forEach(group => {
        const trigger = group.querySelector('.sidebar-coll-trigger');
        const flyout = group.querySelector('[data-flyout]');
        // Single-link groups: clicking icon goes to that link
        const links = group.querySelectorAll('.sidebar-flyout-link');
        if (links.length === 1) {
          trigger.addEventListener('click', () => window.location.href = links[0].getAttribute('href'));
        }
        trigger.addEventListener('mouseenter', () => show(trigger, flyout));
        trigger.addEventListener('mouseleave', () => { hideTimer = setTimeout(hide, 200); });
        flyout.addEventListener('mouseenter', () => clearTimeout(hideTimer));
        flyout.addEventListener('mouseleave', () => { hideTimer = setTimeout(hide, 200); });
        flyout.querySelectorAll('a[href]').forEach(a => a.addEventListener('click', hide));
      });

      // Hide on scroll/resize/uncollapse
      window.addEventListener('scroll', hide, true);
      window.addEventListener('resize', hide);
      new MutationObserver(hide).observe(document.body, { attributes: true, attributeFilter: ['class'] });
      new MutationObserver(hide).observe(sidebar, { attributes: true, attributeFilter: ['class'] });
    })();

    /* Build horizontal menu bar — renders only when body.horizontal-mode */
    (function initHorizontalMenu() {
      if (!window.LINER_MENU) return;
      // Avoid duplicate injection
      if (document.querySelector('.horizontal-bar')) return;
      const current = (window.location.pathname.split('/').pop() || 'dashboard.html');

      const bar = document.createElement('div');
      bar.className = 'horizontal-bar';
      bar.innerHTML = window.LINER_MENU.map((g, gi) => {
        const allPaths = [];
        g.items.forEach(it => {
          if (it.children) it.children.forEach(c => allPaths.push(c.path));
          else if (it.path) allPaths.push(it.path);
        });
        const isActive = allPaths.includes(current);
        // Flat items for dropdown
        const flatHtml = g.items.map(it => {
          if (it.children) {
            return `<div class="hbar-section">${it.label}</div>` +
              it.children.map(c => `<a href="${c.path}" class="hbar-link${c.path === current ? ' active' : ''}"><i data-lucide="${c.icon}" class="lucide-sm"></i><span>${c.label}</span></a>`).join('');
          }
          return `<a href="${it.path}" class="hbar-link${it.path === current ? ' active' : ''}"><i data-lucide="${it.icon}" class="lucide-sm"></i><span>${it.label}</span></a>`;
        }).join('');
        // Single-link group: direct link, no chevron, no dropdown
        if (g.items.length === 1 && g.items[0].path) {
          return `<a href="${g.items[0].path}" class="hbar-item${g.items[0].path === current ? ' active' : ''}"><i data-lucide="${g.groupIcon}" class="lucide-sm"></i><span>${g.group}</span></a>`;
        }
        return `<div class="hbar-group" data-hbar-group="${gi}">
          <button type="button" class="hbar-item${isActive ? ' active' : ''}">
            <i data-lucide="${g.groupIcon}" class="lucide-sm"></i>
            <span>${g.group}</span>
            <i data-lucide="chevron-down" class="lucide-xs hbar-chev"></i>
          </button>
          <div class="hbar-dropdown" data-hbar-dropdown>${flatHtml}</div>
        </div>`;
      }).join('');

      document.body.appendChild(bar);
      window.lucide && lucide.createIcons();

      // Flyout positioning + hover handlers (matches React's portal-based dropdown)
      let activeDrop = null;
      let hideTimer = null;
      function hide() {
        if (activeDrop) {
          activeDrop.classList.remove('hbar-open');
          activeDrop.style.cssText = '';
          activeDrop.closest('.hbar-group')?.querySelector('.hbar-item')?.classList.remove('hbar-trigger-open');
          activeDrop = null;
        }
      }
      function show(trigger, drop) {
        if (activeDrop && activeDrop !== drop) hide();
        clearTimeout(hideTimer);
        const rect = trigger.getBoundingClientRect();
        drop.classList.add('hbar-open');
        trigger.classList.add('hbar-trigger-open');
        const dropdownWidth = 220;
        const isRtl = document.documentElement.dir === 'rtl';
        const maxH = Math.max(window.innerHeight - rect.bottom - 8, 200);
        Object.assign(drop.style, {
          position: 'fixed',
          top: (rect.bottom + 4) + 'px',
          zIndex: '9999',
          maxHeight: maxH + 'px',
        });
        if (isRtl) {
          drop.style.right = Math.max(0, window.innerWidth - rect.right) + 'px';
        } else {
          drop.style.left = Math.min(rect.left, window.innerWidth - dropdownWidth - 8) + 'px';
        }
        activeDrop = drop;
      }
      bar.querySelectorAll('.hbar-group').forEach(group => {
        const trigger = group.querySelector('.hbar-item');
        const drop = group.querySelector('[data-hbar-dropdown]');
        group.addEventListener('mouseenter', () => show(trigger, drop));
        group.addEventListener('mouseleave', () => { hideTimer = setTimeout(hide, 180); });
        drop.querySelectorAll('a[href]').forEach(a => a.addEventListener('click', hide));
      });
      window.addEventListener('scroll', hide, true);
      window.addEventListener('resize', hide);
    })();
  }

  // Sidebar/topbar/settings markup is INLINE in each HTML page now.
  // This init only wires up dynamic state: active link, breadcrumbs, dark-icon, events.
  window.LinerLayout = {
    init() {
      const current = getCurrentSlug();

      // Mobile: prepend logo before the mobile menu button (matches React)
      const mobileMenuBtn = document.querySelector('.app-header [data-toggle-mobile-sidebar]');
      if (mobileMenuBtn && !document.querySelector('.app-header .header-mobile-logo')) {
        const logo = document.createElement('a');
        logo.href = 'dashboard.html';
        logo.className = 'header-mobile-logo d-lg-none';
        logo.setAttribute('aria-label', 'Liner Admin');
        logo.innerHTML = '<img src="img/logo.svg" alt="Liner" width="28" height="28">';
        mobileMenuBtn.parentNode.insertBefore(logo, mobileMenuBtn);
      }

      // Header search — replace always-visible input with click-to-expand pattern (matches React)
      const oldSearch = document.querySelector('.app-header .search-wrap');
      if (oldSearch && !document.querySelector('.app-header .hdr-search')) {
        const wrap = document.createElement('div');
        wrap.className = 'hdr-search';
        wrap.innerHTML = `
          <button type="button" class="header-icon-btn hdr-search-btn" aria-label="Search" data-hdr-search-open>${ICON('search', 'lucide-md')}</button>
          <div class="hdr-search-input hidden" data-hdr-search-panel>
            <input type="text" class="hdr-search-field" placeholder="Search..." data-hdr-search-field>
            <button type="button" class="header-icon-btn hdr-search-close" aria-label="Close" data-hdr-search-close>${ICON('x', 'lucide-md')}</button>
          </div>`;
        oldSearch.replaceWith(wrap);

        const openBtn = wrap.querySelector('[data-hdr-search-open]');
        const closeBtn = wrap.querySelector('[data-hdr-search-close]');
        const panel = wrap.querySelector('[data-hdr-search-panel]');
        const field = wrap.querySelector('[data-hdr-search-field]');

        const open = () => {
          openBtn.classList.add('hidden');
          panel.classList.remove('hidden');
          setTimeout(() => field.focus(), 30);
        };
        const close = () => {
          panel.classList.add('hidden');
          openBtn.classList.remove('hidden');
          field.value = '';
        };
        openBtn.addEventListener('click', open);
        closeBtn.addEventListener('click', close);
        field.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
        // Click outside collapses
        document.addEventListener('mousedown', (e) => {
          if (!wrap.contains(e.target) && !panel.classList.contains('hidden')) close();
        });
      }

      // Active nav link + expand parent submenu when a child is active
      document.querySelectorAll('.sidebar-nav-link[data-path]').forEach(a => {
        if (a.dataset.path === current) a.classList.add('active');
      });
      document.querySelectorAll('.sidebar-parent[data-paths]').forEach(p => {
        const paths = p.dataset.paths.split(',');
        if (paths.includes(current)) {
          p.classList.add('expanded');
          p.nextElementSibling?.classList.add('open');
        }
      });
      // Breadcrumbs (uses optional BREADCRUMBS map fallback to <title>)
      const crumbHost = document.querySelector('[data-breadcrumb]');
      if (crumbHost) {
        const crumbs = BREADCRUMBS[current] || [document.title.replace(' | Liner Admin','')];
        let html = `<span class="text-muted">Home</span>`;
        crumbs.forEach((c, i) => {
          html += ` ${ICON('chevron-right', 'lucide-sm')} <span class="${i === crumbs.length - 1 ? 'crumb-last' : 'text-muted'}">${c}</span>`;
        });
        crumbHost.innerHTML = html;
      }
      // Dark-mode icon
      document.querySelectorAll('[data-toggle-dark] i').forEach(i => i.setAttribute('data-lucide', LinerTheme.isDark ? 'sun' : 'moon'));
      // Collapsed state on inline sidebar
      if (LinerTheme.collapsed) document.querySelector('.app-sidebar')?.classList.add('collapsed');
      // Color preset active swatch
      document.querySelectorAll('[data-color]').forEach(b => {
        if (b.dataset.color === LinerTheme.color) b.classList.add('active', 'outline-' + LinerTheme.color);
      });
      // Color mode active card
      document.querySelectorAll('[data-mode]').forEach(b => b.classList.toggle('active', b.dataset.mode === (LinerTheme.isDark ? 'dark' : 'light')));
      // Layout switches active state
      document.querySelectorAll('[data-setting]').forEach(s => {
        const key = s.dataset.setting;
        const on = (key === 'collapsed' && LinerTheme.collapsed) || (key === 'horizontal' && LinerTheme.horizontal) || (key === 'rtl' && LinerTheme.isRTL);
        if (on) s.classList.add('on');
      });
      if (window.lucide) lucide.createIcons();
      bindEvents();
    }
  };
})();

document.addEventListener('DOMContentLoaded', () => window.LinerLayout && LinerLayout.init());


/* ============================================================
   PER-PAGE DISPATCHERS
   ============================================================ */
(function () {
  function slug() {
    const p = window.location.pathname.replace(/\\/g, '/');
    const f = p.substring(p.lastIndexOf('/') + 1) || 'dashboard.html';
    return (f === 'index.html' || f === '') ? 'dashboard.html' : f;
  }
  window.LINER_PAGE = slug();
})();

/* ===== Page: list ===== */
if (window.LINER_PAGE === 'list.html') {
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-ls-interactive]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-ls-interactive]').forEach(b => b.classList.remove('ls-interactive-active'));
      btn.classList.add('ls-interactive-active');
    });
  });
  document.querySelectorAll('[data-ls-check]').forEach(btn => {
    btn.addEventListener('click', () => btn.classList.toggle('ls-checked'));
  });
  window.lucide && lucide.createIcons();
});

}

/* ===== Page: dialog ===== */
if (window.LINER_PAGE === 'dialog.html') {
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-dialog-open]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.dialogOpen;
      const dlg = document.getElementById('dlg-' + id);
      if (dlg) dlg.classList.remove('hidden');
    });
  });
  document.querySelectorAll('[data-dialog-close]').forEach(el => {
    el.addEventListener('click', () => {
      const dlg = el.closest('.dlg');
      if (dlg) dlg.classList.add('hidden');
    });
  });
  // Esc key closes any open dialog
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.dlg:not(.hidden)').forEach(d => d.classList.add('hidden'));
    }
  });
  window.lucide && lucide.createIcons();
});

}

/* ===== Page: accordion ===== */
if (window.LINER_PAGE === 'accordion.html') {
document.addEventListener('DOMContentLoaded', () => {
  // Each accordion group: if the closest [data-ac-multi] ancestor exists, multiple-open is allowed.
  // Otherwise, opening one item closes other siblings of the same parent.
  document.querySelectorAll('.ac-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.parentElement; // .ac-item / .ac-item-bordered / etc.
      const parent = item.parentElement;
      const isMulti = !!item.closest('[data-ac-multi]');
      const isOpen = item.classList.contains('ac-open');
      if (!isMulti) {
        // Close siblings that share the same parent container
        Array.from(parent.children).forEach(child => {
          if (child !== item) child.classList.remove('ac-open');
        });
      }
      item.classList.toggle('ac-open', !isOpen);
    });
  });
  window.lucide && lucide.createIcons();
});

}

/* ===== Page: analytics ===== */
if (window.LINER_PAGE === 'analytics.html') {
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors(); const B = LinerApexBase();

  new ApexCharts(document.querySelector('#an-page-views'), {
    ...B, chart: { ...B.chart, type: 'area', height: 300 },
    series: [
      { name: 'Page Views',      data: [12400,14200,11800,13600,15900,18200,16400,19800,21200,23400,22100] },
      { name: 'Unique Visitors', data: [8200,9400,7800,9100,10500,12100,10900,13200,14100,15600,14700] },
    ],
    colors: [C.primary, C.cyan],
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: .2, opacityTo: 0 } },
    dataLabels: { enabled: false },
    xaxis: { ...B.xaxis, categories: ['Mar 1','Mar 2','Mar 3','Mar 4','Mar 5','Mar 6','Mar 7','Mar 8','Mar 9','Mar 10','Mar 11'] },
    yaxis: { ...B.yaxis, labels: { ...B.yaxis.labels, formatter: v => (v/1000).toFixed(0) + 'k' } },
    legend: { ...B.legend, position: 'top', horizontalAlign: 'right', markers: { width: 8, height: 8, radius: 9 }, fontSize: '12px' },
  }).render();

  new ApexCharts(document.querySelector('#an-radar'), {
    chart: { type: 'radar', height: 240, toolbar: { show: false }, fontFamily: 'Inter', foreColor: C.text },
    series: [{ name: 'Score', data: [82, 68, 75, 59, 88, 71] }],
    labels: ['Engagement','Retention','Acquisition','Conversion','Satisfaction','Revenue'],
    colors: [C.primary], fill: { opacity: 0.3 }, stroke: { width: 2 }, markers: { size: 3 },
    yaxis: { show: false, max: 100 },
    xaxis: { labels: { style: { colors: C.axis, fontSize: '11px' } } },
    plotOptions: { radar: { polygons: { strokeColors: C.grid, fill: { colors: ['transparent'] } } } },
  }).render();
  // Fix radar fill opacity (Apex ignores fillOpacity in JS for radar — CSS override)
  const styleId = '__an-radar-fix';
  if (!document.getElementById(styleId)) {
    const s = document.createElement('style');
    s.id = styleId;
    s.textContent = '.apexcharts-radar-series .apexcharts-series path{fill-opacity:.25!important}';
    document.head.appendChild(s);
  }

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: area-chart ===== */
if (window.LINER_PAGE === 'area-chart.html') {
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors(); const B = LinerApexBase();
  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

  // Monthly Revenue (gradient-filled area)
  new ApexCharts(document.querySelector('#ac-revenue'), {
    ...B, chart:{ ...B.chart, type:'area', height:280 },
    series:[{ name:'Revenue', data:[38000,42000,39000,51000,47000,58000,62000,55000,67000,71000,78000,84000] }],
    colors:[C.primary],
    stroke:{ curve:'smooth', width:2.5 },
    fill:{ type:'gradient', gradient:{ shadeIntensity:1, opacityFrom:.35, opacityTo:0, stops:[5, 95] } },
    dataLabels:{ enabled:false },
    xaxis:{ ...B.xaxis, categories: months },
    yaxis:{ ...B.yaxis, labels:{ ...B.yaxis.labels, formatter:(v)=>'$'+v/1000+'k' } },
  }).render();

  // Stacked Area
  new ApexCharts(document.querySelector('#ac-stacked'), {
    ...B, chart:{ ...B.chart, type:'area', height:280, stacked:true },
    series:[
      { name:'Online',  data:[4200,3800,5100,4700,6200,7100] },
      { name:'Offline', data:[2800,3100,2600,3400,2900,3200] },
      { name:'Mobile',  data:[1600,1900,2200,2500,2800,3100] },
    ],
    colors:[C.primary, C.cyan, C.emerald],
    stroke:{ curve:'smooth', width:2 },
    fill:{ type:'gradient', gradient:{ opacityFrom:.5, opacityTo:.1 } },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories:['Jan','Feb','Mar','Apr','May','Jun'] },
    yaxis:{ ...B.yaxis, labels:{ ...B.yaxis.labels, formatter:(v)=>v/1000+'k' } },
  }).render();

  // Revenue vs Target
  new ApexCharts(document.querySelector('#ac-multi'), {
    ...B, chart:{ ...B.chart, type:'area', height:280 },
    series:[
      { name:'Revenue', data:[38000,42000,39000,51000,47000,58000,62000,55000,67000,71000,78000,84000] },
      { name:'Target',  data:[35000,38000,40000,44000,46000,50000,54000,58000,60000,64000,68000,72000] },
    ],
    colors:[C.primary, C.amber],
    stroke:{ curve:'smooth', width:[2.5, 2], dashArray:[0, 5] },
    fill:{ type:'gradient', gradient:{ opacityFrom:.25, opacityTo:0 } },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories: months },
    yaxis:{ ...B.yaxis, labels:{ ...B.yaxis.labels, formatter:(v)=>'$'+v/1000+'k' } },
  }).render();

  // Profit & Loss (with negative values)
  new ApexCharts(document.querySelector('#ac-negative'), {
    ...B, chart:{ ...B.chart, type:'area', height:280 },
    series:[{ name:'Profit', data:[2400,-800,3200,1600,-1200,4100,2800,-400,5200,3600,-600,6400] }],
    colors:[C.emerald],
    stroke:{ curve:'smooth', width:2.5 },
    fill:{ type:'gradient', gradient:{ opacityFrom:.4, opacityTo:.05 } },
    markers:{ size:3, strokeWidth:0, colors:[C.emerald] },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories: months },
    yaxis:{ ...B.yaxis, labels:{ ...B.yaxis.labels, formatter:(v)=>'$'+(v/1000).toFixed(0)+'k' } },
  }).render();

  // Sparklines (gradient area)
  const spark = (id, color, data) => {
    new ApexCharts(document.querySelector('#' + id), {
      chart:{ type:'area', height:60, sparkline:{ enabled:true } },
      series:[{ data }],
      colors:[color],
      stroke:{ curve:'smooth', width:1.5 },
      fill:{ type:'gradient', gradient:{ opacityFrom:.3, opacityTo:0 } },
    }).render();
  };
  spark('ac-spark-visitors', C.primary, [28,35,30,42,38,50,46]);
  spark('ac-spark-sessions', C.cyan,    [18,22,19,28,24,32,29]);
  spark('ac-spark-views',    C.emerald, [55,62,58,71,66,78,74]);
  spark('ac-spark-bounce',   C.rose,    [62,58,60,54,56,51,49]);

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: bar-chart ===== */
if (window.LINER_PAGE === 'bar-chart.html') {
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors(); const B = LinerApexBase();

  // Monthly Sales (Grouped vertical)
  new ApexCharts(document.querySelector('#bc-monthly'), {
    ...B, chart:{ ...B.chart, type:'bar', height:280 },
    series:[
      { name:'Online',  data:[4200,3800,5100,4700,6200,7100,6800,7400] },
      { name:'Offline', data:[2800,3100,2600,3400,2900,3200,3500,3800] },
    ],
    colors:[C.primary, C.cyan],
    plotOptions:{ bar:{ borderRadius:4, columnWidth:'55%', borderRadiusApplication:'end' } },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'] },
    yaxis:{ ...B.yaxis, labels:{ ...B.yaxis.labels, formatter:(v)=>v/1000+'k' } },
  }).render();

  // Department Revenue (Horizontal)
  new ApexCharts(document.querySelector('#bc-dept'), {
    ...B, chart:{ ...B.chart, type:'bar', height:280 },
    series:[{ name:'Revenue', data:[128000,96000,74000,62000,48000,36000] }],
    colors:[C.primary, C.cyan, C.emerald, C.amber, C.violet, C.rose],
    plotOptions:{ bar:{ horizontal:true, borderRadius:6, barHeight:'60%', distributed:true, borderRadiusApplication:'end' } },
    dataLabels:{ enabled:false },
    legend:{ show:false },
    xaxis:{ ...B.xaxis, categories:['Engineering','Sales','Marketing','Design','Support','HR'], labels:{ ...B.xaxis.labels, formatter:(v)=>'$'+v/1000+'k' } },
  }).render();

  // Stacked Product Categories
  new ApexCharts(document.querySelector('#bc-stacked'), {
    ...B, chart:{ ...B.chart, type:'bar', height:280, stacked:true },
    series:[
      { name:'Electronics', data:[3200,2800,4100,3700,5200,4800] },
      { name:'Clothing',    data:[2100,2500,2900,2300,3100,3400] },
      { name:'Furniture',   data:[1400,1200,1800,2100,1600,2200] },
    ],
    colors:[C.primary, C.cyan, C.emerald],
    plotOptions:{ bar:{ borderRadius:4, borderRadiusApplication:'end', borderRadiusWhenStacked:'last' } },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories:['Jan','Feb','Mar','Apr','May','Jun'] },
    yaxis:{ ...B.yaxis, labels:{ ...B.yaxis.labels, formatter:(v)=>v/1000+'k' } },
  }).render();

  // Diverging Bar (P&L)
  const divData = [32,18,-12,45,-28,-15,-22,18];
  new ApexCharts(document.querySelector('#bc-diverging'), {
    ...B, chart:{ ...B.chart, type:'bar', height:280 },
    series:[{ name:'Change %', data:divData }],
    plotOptions:{ bar:{ horizontal:true, borderRadius:4, barHeight:'60%', distributed:true, borderRadiusApplication:'end',
      colors:{ ranges:[
        { from:-100, to:-0.001, color:C.rose },
        { from:0, to:100, color:C.emerald },
      ] }
    } },
    dataLabels:{ enabled:false },
    legend:{ show:false },
    xaxis:{ ...B.xaxis, categories:['Q1 Revenue','Q2 Revenue','Q3 Revenue','Q4 Revenue','Marketing','Operations','R&D','Net Profit'], labels:{ ...B.xaxis.labels, formatter:(v)=>v+'%' } },
    yaxis:{ ...B.yaxis, labels:{ ...B.yaxis.labels, style:{ fontSize:'10px', colors:C.axis } } },
  }).render();

  // Mini Bar KPI Cards
  const miniBar = (id, color, data) => {
    new ApexCharts(document.querySelector('#' + id), {
      chart:{ type:'bar', height:60, sparkline:{ enabled:true } },
      series:[{ data }],
      colors:[color],
      plotOptions:{ bar:{ columnWidth:'55%', borderRadius:3, borderRadiusApplication:'end' } },
    }).render();
  };
  miniBar('bc-spark-sales',   C.primary, [42,38,55,47,62,58,71]);
  miniBar('bc-spark-orders',  C.cyan,    [18,22,19,28,24,32,29]);
  miniBar('bc-spark-revenue', C.emerald, [8,12,9,15,11,18,14]);
  miniBar('bc-spark-users',   C.violet,  [32,28,41,36,48,44,52]);

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: blog-posts (markup inline) ===== */
/* ===== Page: blog-detail ===== */
if (window.LINER_PAGE === 'blog-detail.html') {
document.addEventListener('DOMContentLoaded', () => {
  const likeBtn = document.getElementById('bd-like');
  const likeCount = document.getElementById('bd-like-count');
  likeBtn?.addEventListener('click', () => {
    const liked = likeBtn.dataset.liked === 'true';
    likeBtn.dataset.liked = !liked;
    likeCount.textContent = liked ? '142' : '143';
  });
  const bookmark = document.getElementById('bd-bookmark');
  bookmark?.addEventListener('click', () => {
    bookmark.dataset.bookmarked = bookmark.dataset.bookmarked === 'true' ? 'false' : 'true';
  });
  window.lucide && lucide.createIcons();
});

}

if (window.LINER_PAGE === 'blog-posts.html') {
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-bp-cat]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-bp-cat]').forEach(b => b.classList.remove('bp-cat-active'));
      btn.classList.add('bp-cat-active');
    });
  });
  window.lucide && lucide.createIcons();
});

}

/* ===== Page: column-chart ===== */
if (window.LINER_PAGE === 'column-chart.html') {
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors(); const B = LinerApexBase();
  const months = ['Jan','Feb','Mar','Apr','May','Jun'];
  const revenue  = [4200,3800,5200,4700,6100,5800];
  const expenses = [2800,2400,3100,2900,3500,3200];
  const profit   = [1400,1400,2100,1800,2600,2600];

  // Basic
  new ApexCharts(document.querySelector('#cc-basic'), {
    ...B, chart:{ ...B.chart, type:'bar', height:280 },
    series:[{ name:'Revenue', data:revenue }],
    colors:[C.primary],
    plotOptions:{ bar:{ borderRadius:6, columnWidth:'50%', borderRadiusApplication:'end' } },
    dataLabels:{ enabled:false },
    xaxis:{ ...B.xaxis, categories: months },
    yaxis:{ ...B.yaxis },
  }).render();

  // Grouped
  new ApexCharts(document.querySelector('#cc-grouped'), {
    ...B, chart:{ ...B.chart, type:'bar', height:280 },
    series:[
      { name:'Revenue', data:revenue },
      { name:'Expenses', data:expenses },
    ],
    colors:[C.primary, C.cyan],
    plotOptions:{ bar:{ borderRadius:4, columnWidth:'55%', borderRadiusApplication:'end' } },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories: months },
  }).render();

  // Stacked
  new ApexCharts(document.querySelector('#cc-stacked'), {
    ...B, chart:{ ...B.chart, type:'bar', height:280, stacked:true },
    series:[
      { name:'Profit', data:profit },
      { name:'Expenses', data:expenses },
    ],
    colors:[C.emerald, C.primary],
    plotOptions:{ bar:{ borderRadius:6, columnWidth:'55%', borderRadiusApplication:'end', borderRadiusWhenStacked:'last' } },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories: months },
  }).render();

  // Color-Coded
  new ApexCharts(document.querySelector('#cc-colored'), {
    ...B, chart:{ ...B.chart, type:'bar', height:280 },
    series:[{ name:'Revenue', data:revenue }],
    colors:[C.primary, C.cyan, C.emerald, C.amber, C.violet, C.rose],
    plotOptions:{ bar:{ borderRadius:6, columnWidth:'50%', distributed:true, borderRadiusApplication:'end' } },
    dataLabels:{ enabled:false },
    legend:{ show:false },
    xaxis:{ ...B.xaxis, categories: months },
  }).render();

  // Rounded
  new ApexCharts(document.querySelector('#cc-rounded'), {
    ...B, chart:{ ...B.chart, type:'bar', height:280 },
    series:[
      { name:'Revenue', data:revenue },
      { name:'Profit', data:profit },
    ],
    colors:[C.violet, C.rose],
    plotOptions:{ bar:{ borderRadius:8, columnWidth:'55%', borderRadiusApplication:'end' } },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories: months },
  }).render();

  // Gradient
  new ApexCharts(document.querySelector('#cc-gradient'), {
    ...B, chart:{ ...B.chart, type:'bar', height:280 },
    series:[
      { name:'Revenue', data:revenue },
      { name:'Expenses', data:expenses },
    ],
    colors:[C.primary, C.amber],
    plotOptions:{ bar:{ borderRadius:6, columnWidth:'55%', borderRadiusApplication:'end' } },
    fill:{ type:'gradient', gradient:{ shade:'light', type:'vertical', gradientToColors:[C.cyan, C.rose], shadeIntensity:1, opacityFrom:1, opacityTo:0.7, stops:[0,100] } },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories: months },
  }).render();

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: custom-icons ===== */
if (window.LINER_PAGE === 'custom-icons.html') {
document.addEventListener('DOMContentLoaded', () => {
  const names = ['house','person','gear','star','heart','bell','calendar','envelope','search','cart','bag','box','cloud','sun','moon','image','file-earmark','folder','lock','key','phone','camera','book','flag','tag','pencil','trash','download','upload','globe','wifi','battery','mic','speaker','play','pause','plus','dash','x','check','arrow-up','arrow-down','arrow-left','arrow-right','chevron-up','chevron-down','chevron-left','chevron-right'];
  document.getElementById('icon-grid').innerHTML = names.map(n => '<div class="col-6 col-md-3 col-lg-2"><div class="icon-tile"><i class="bi bi-' + n + ' bi-icon"></i><div class="icon-tile-label">' + n + '</div></div></div>').join('');
  window.lucide && lucide.createIcons();
});

}

/* ===== Page: dashboard ===== */
if (window.LINER_PAGE === 'dashboard.html') {
/* Modern Dashboard — chart init only; markup lives in dashboard.html */
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors();
  const B = LinerApexBase();

  new ApexCharts(document.querySelector('#chart-audience'), {
    ...B, chart: { ...B.chart, height: 220, type: 'area' },
    series: [
      { name: 'Current',  data: [680,720,650,810,760,830,780,850,720,790,860,810,740,880,830,900] },
      { name: 'Previous', data: [520,580,610,550,620,590,640,560,600,630,570,610,650,580,620,590] }
    ],
    colors: [C.primary, C.violet],
    stroke: { curve: 'smooth', width: 2, dashArray: [0, 4] },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .35, opacityTo: 0 } },
    dataLabels: { enabled: false },
    xaxis: { ...B.xaxis, categories: ['1','3','5','7','9','11','13','15','17','19','21','23','25','27','29','31'] },
    legend: { show: false },
  }).render();

  new ApexCharts(document.querySelector('#spark-bounce'), {
    chart: { type: 'area', height: 60, sparkline: { enabled: true } },
    series: [{ data: [38,35,40,36,34,32,35,30,28,33,31,29,26,24] }],
    colors: [C.cyan], stroke: { width: 2, curve: 'smooth' },
    fill: { type: 'gradient', gradient: { opacityFrom: .4, opacityTo: 0 } },
  }).render();
  new ApexCharts(document.querySelector('#spark-users'), {
    chart: { type: 'bar', height: 60, sparkline: { enabled: true } },
    series: [{ data: [60,75,50,85,70,90,65,80,55,95,70,45] }],
    colors: [C.primary],
    plotOptions: { bar: { borderRadius: 2, columnWidth: '60%' } },
  }).render();
  new ApexCharts(document.querySelector('#spark-sessions'), {
    chart: { type: 'bar', height: 80, sparkline: { enabled: true }, stacked: true },
    series: [
      { name: 'A', data: [40,55,35,60,50,70,45,65,55,75] },
      { name: 'B', data: [30,25,40,35,30,20,35,25,30,20] }
    ],
    colors: [C.primary, C.violet],
    plotOptions: { bar: { borderRadius: 2, columnWidth: '50%' } },
  }).render();

  new ApexCharts(document.querySelector('#chart-revenue'), {
    ...B, chart: { ...B.chart, height: 320, type: 'line' },
    series: [
      { name: 'Profit',   type: 'column', data: [12800,16340,13650,19800,17640,24380,20160,28060,25520,31490,34560,42500] },
      { name: 'Expenses', type: 'column', data: [19200,21660,21350,25200,24360,28620,27840,32940,32480,35510,37440,42500] },
      { name: 'Target',   type: 'line',   data: [30000,35000,38000,42000,44000,48000,50000,55000,58000,62000,68000,75000] },
    ],
    colors: [C.emerald, C.primary, C.amber],
    stroke: { width: [0,0,3], dashArray: [0,0,5], curve: 'smooth' },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
    dataLabels: { enabled: false },
    xaxis: { ...B.xaxis, categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] },
    yaxis: { ...B.yaxis, labels: { ...B.yaxis.labels, formatter: v => '$' + (v/1000).toFixed(0) + 'k' } },
    legend: { show: false },
  }).render();

  new ApexCharts(document.querySelector('#chart-radar'), {
    chart: { type: 'radar', height: 240, toolbar: { show: false }, fontFamily: 'Inter', dropShadow: { enabled: false } },
    series: [{ name: 'Score', data: [82, 68, 75, 59, 88, 71] }],
    labels: ['Engagement','Retention','Acquisition','Conversion','Satisfaction','Revenue'],
    colors: [C.primary],
    fill: { type: 'solid', opacity: 1, colors: [C.primary] },
    stroke: { width: 2, colors: [C.primary] },
    markers: { size: 4, colors: [C.primary], strokeColors: '#fff', strokeWidth: 2 },
    yaxis: { show: true, min: 0, max: 100, tickAmount: 5, labels: { style: { fontSize: '9px', colors: C.axis } } },
    xaxis: { labels: { style: { fontSize: '11px', colors: Array(6).fill(C.axis) } } },
    plotOptions: { radar: { polygons: { strokeColors: C.grid, connectorColors: C.grid, fill: { colors: ['transparent','transparent'] } } } },
    tooltip: { enabled: true },
    legend: { show: false },
  }).render();

  new ApexCharts(document.querySelector('#chart-visitors'), {
    ...B, chart: { ...B.chart, type: 'bar', height: 240 },
    series: [
      { name: 'New',       data: [420,380,510,470,620,390,280] },
      { name: 'Returning', data: [780,690,820,760,910,580,450] },
    ],
    colors: [C.primary, C.cyan],
    plotOptions: { bar: { borderRadius: 4, columnWidth: '40%' } },
    dataLabels: { enabled: false },
    xaxis: { ...B.xaxis, categories: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] },
    legend: { show: false },
  }).render();

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: donut-chart ===== */
if (window.LINER_PAGE === 'donut-chart.html') {
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors(); const B = LinerApexBase();

  // Category Breakdown (donut)
  const catData   = [38, 24, 18, 12, 8];
  const catLabels = ['Electronics','Clothing','Home & Garden','Sports','Books'];
  const catColors = [C.primary, C.cyan, C.emerald, C.amber, C.violet];
  new ApexCharts(document.querySelector('#dc-category'), {
    chart:{ type:'donut', height:220, fontFamily:'Inter, sans-serif', toolbar:{ show:false } },
    series:catData,
    labels:catLabels,
    colors:catColors,
    stroke:{ width:3, colors:['#fff'] },
    dataLabels:{ enabled:false },
    legend:{ show:false },
    plotOptions:{ pie:{ donut:{ size:'62%' } } },
    tooltip:{ y:{ formatter:(v)=>v+'%' } },
  }).render();
  // Render right-side legend
  const legend = document.getElementById('dc-category-legend');
  if (legend) {
    legend.innerHTML = catLabels.map((label, i) => {
      const v = catData[i]; const color = catColors[i];
      return `<div>
        <div class="flex items-center justify-between mb-1">
          <div class="flex items-center gap-2"><span class="w-2-5 h-2-5 rounded-full shrink-0" style="background-color:${color}"></span><span class="text-sm text-slate-700">${label}</span></div>
          <span class="text-sm font-semibold text-slate-800">${v}%</span>
        </div>
        <div class="h-1-5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full rounded-full" style="width:${v}%;background-color:${color}"></div></div>
      </div>`;
    }).join('');
  }

  // Traffic Sources (pie with hover highlight)
  new ApexCharts(document.querySelector('#dc-traffic'), {
    chart:{ type:'pie', height:300, fontFamily:'Inter, sans-serif', toolbar:{ show:false } },
    series:[42, 28, 18, 12],
    labels:['Direct','Organic','Referral','Paid'],
    colors:[C.primary, C.cyan, C.emerald, C.amber],
    stroke:{ width:3, colors:['#fff'] },
    dataLabels:{ enabled:false },
    legend:{ position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px', labels:{ colors:C.text } },
    tooltip:{ y:{ formatter:(v)=>v+'%' } },
  }).render();

  // Annual Goal Progress (Semi-donut)
  const goal = 73;
  new ApexCharts(document.querySelector('#dc-goal'), {
    chart:{ type:'donut', height:200, fontFamily:'Inter, sans-serif', toolbar:{ show:false } },
    series:[goal, 100 - goal],
    labels:['Achieved','Remaining'],
    colors:[C.emerald, C.grid],
    stroke:{ width:3, colors:['#fff'] },
    dataLabels:{ enabled:false },
    legend:{ show:false },
    plotOptions:{ pie:{ startAngle:-90, endAngle:90, donut:{ size:'62%', labels:{ show:true, total:{ show:true, label:'of annual goal', fontSize:'12px', color:C.axis, formatter:()=>goal+'%' }, value:{ show:false }, name:{ show:false } } } } },
    tooltip:{ y:{ formatter:(v)=>v+'%' } },
  }).render();

  // Nested Ring (Device + Browser)
  // ApexCharts doesn't have native nested pies — emulate with two side-by-side donut charts merged, or use one with custom data.
  // Use a polar-area-style emulation: outer donut for device, inner donut overlapped via CSS.
  new ApexCharts(document.querySelector('#dc-nested'), {
    chart:{ type:'donut', height:320, fontFamily:'Inter, sans-serif', toolbar:{ show:false } },
    series:[52, 32, 16, 40, 25, 18, 12, 5],
    labels:['Desktop','Mobile','Tablet','Chrome','Safari','Firefox','Edge','Other'],
    colors:[C.primary, C.cyan, C.emerald, C.primary, C.cyan, C.emerald, C.amber, C.violet],
    stroke:{ width:2, colors:['#fff'] },
    dataLabels:{ enabled:false },
    legend:{ position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px', labels:{ colors:C.text } },
    plotOptions:{ pie:{ donut:{ size:'45%' } } },
    tooltip:{ y:{ formatter:(v)=>v+'%' } },
  }).render();

  // Mini donuts (storage, cpu, memory)
  const miniDonut = (id, percent, color) => {
    new ApexCharts(document.querySelector('#' + id), {
      chart:{ type:'donut', height:120, width:120, fontFamily:'Inter, sans-serif', toolbar:{ show:false } },
      series:[percent, 100 - percent],
      labels:['Used','Free'],
      colors:[color, C.grid],
      stroke:{ width:2, colors:['#fff'] },
      dataLabels:{ enabled:false },
      legend:{ show:false },
      plotOptions:{ pie:{ donut:{ size:'68%' } } },
      tooltip:{ enabled:false },
    }).render();
  };
  miniDonut('dc-mini-storage', 72, C.primary);
  miniDonut('dc-mini-cpu',     45, C.cyan);
  miniDonut('dc-mini-memory',  68, C.amber);

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: ecommerce-list (markup inline) ===== */
if (window.LINER_PAGE === 'ecommerce-list.html') {
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-el-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-el-filter]').forEach(b => b.classList.remove('inv-filter-active'));
      btn.classList.add('inv-filter-active');
    });
  });
  window.lucide && lucide.createIcons();
});

}

/* ===== Page: ecommerce ===== */
if (window.LINER_PAGE === 'ecommerce.html') {
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors(); const B = LinerApexBase();

  // Chart init only; all markup lives directly in ecommerce.html
  new ApexCharts(document.querySelector('#chart-revenue-updates'), {
    ...B, chart: { ...B.chart, type:'bar', height:280 },
    series: [
      { name:'Online',  data:[32000,41000,38000,52000,47000,61000,55000,72000] },
      { name:'Offline', data:[18000,22000,20000,26000,24000,29000,27000,33000] },
    ],
    colors: [C.primary, C.cyan],
    plotOptions: { bar:{ borderRadius:4, columnWidth:'45%' } },
    dataLabels: { enabled:false },
    xaxis: { ...B.xaxis, categories:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'] },
    yaxis: { ...B.yaxis, labels:{ style:{ colors:C.axis }, formatter:v => (v/1000)+'k' } },
    legend: { show:false },
  }).render();

  new ApexCharts(document.querySelector('#chart-yearly'), {
    chart: { type:'donut', height:96, sparkline:{ enabled:true } },
    series: [38,32,30],
    colors: [C.primary, C.violet, C.primary],
    plotOptions: { pie:{ donut:{ size:'62%' } } },
    stroke: { width:0 }, dataLabels: { enabled:false },
    legend: { show:false }, tooltip: { enabled:false },
  }).render();

  new ApexCharts(document.querySelector('#spark-monthly-earnings'), {
    chart: { type:'bar', height:60, sparkline:{ enabled:true } },
    series: [{ data:[25,40,30,55,45,35,50] }],
    colors: [C.primary],
    plotOptions: { bar:{ borderRadius:4, columnWidth:'40%' } },
    tooltip: { enabled:false },
  }).render();

  new ApexCharts(document.querySelector('#chart-cat-share'), {
    chart: { type:'donut', height:120, sparkline:{ enabled:true } },
    series: [38,24,18,12,8],
    colors: [C.primary, C.cyan, C.emerald, C.amber, C.rose],
    plotOptions: { pie:{ donut:{ size:'58%' } } },
    stroke: { width:2 }, dataLabels: { enabled:false },
    legend: { show:false },
  }).render();

  new ApexCharts(document.querySelector('#chart-weekly-revenue'), {
    ...B, chart: { ...B.chart, type:'bar', height:240 },
    series: [
      { name:'Revenue', data:[8200,11400,9800,14200,16800,13200,10600] },
      { name:'Orders',  data:[640,890,760,1120,1310,1040,830] },
    ],
    colors: [C.primary, C.cyan],
    plotOptions: { bar:{ borderRadius:4, columnWidth:'45%' } },
    dataLabels: { enabled:false },
    xaxis: { ...B.xaxis, categories:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] },
    yaxis: { ...B.yaxis, labels:{ style:{ colors:C.axis }, formatter:v => '$'+(v/1000)+'k' } },
    legend: { show:false },
  }).render();

  new ApexCharts(document.querySelector('#chart-sales-channels'), {
    ...B, chart: { ...B.chart, type:'bar', height:220 },
    series: [
      { name:'Online',  data:[32000,41000,38000,52000,47000,61000,55000,72000] },
      { name:'Offline', data:[18000,22000,20000,26000,24000,29000,27000,33000] },
      { name:'Refunds', data:[2400,1800,2100,3200,2700,3800,2900,4200] },
    ],
    colors: [C.primary, C.cyan, C.rose],
    plotOptions: { bar:{ borderRadius:3, columnWidth:'45%' } },
    dataLabels: { enabled:false },
    xaxis: { ...B.xaxis, categories:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'] },
    yaxis: { ...B.yaxis, labels:{ style:{ colors:C.axis }, formatter:v => '$'+(v/1000)+'k' } },
    legend: { show:false },
  }).render();

  // Top Performing Products tab pills
  document.querySelectorAll('[data-prod-tab]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-prod-tab]').forEach(b => b.classList.remove('ec-tab-pill-active'));
      btn.classList.add('ec-tab-pill-active');
    });
  });

  window.lucide && lucide.createIcons();
});

}


/* ===== Page: email (markup is inline) ===== */
if (window.LINER_PAGE === 'email.html') {
document.addEventListener('DOMContentLoaded', () => {
  // ----- Email data (mirrors React Email.jsx) -----
  const EMAILS = {
    1: { from:'Alice Johnson', email:'alice@company.com', subject:'Q1 Product Roadmap Review', time:'10:24 AM', body:`Hi John,\n\nI wanted to share the updated product roadmap for Q1 2026. Please review the attached document and provide your feedback by end of day Friday.\n\nKey highlights:\n- Launch of Liner v2.0 with new dashboard features\n- Mobile app beta release in March\n- API v3 public launch in April\n- Partnership integrations with 5 new vendors\n\nLet me know if you have any questions or concerns.\n\nBest regards,\nAlice`, color:'bg-primary-500', initials:'AJ' },
    2: { from:'Bob Smith', email:'bob@company.com', subject:'PR #247 is ready for review', time:'9:15 AM', body:`Hey John,\n\nI've just pushed the changes for the new chart components. The PR includes:\n\n- Recharts integration with custom tooltips\n- Dark mode support for all charts\n- Responsive chart containers\n- Custom color schemes matching the design system\n\nPR link: https://github.com/liner/admin/pull/247\n\nWould you mind reviewing when you get a chance? I'm available for a quick sync if needed.\n\nCheers,\nBob`, color:'bg-cyan-500', initials:'BS' },
    3: { from:'Stripe', email:'noreply@stripe.com', subject:'Your monthly statement is ready', time:'Yesterday', body:`Hi John,\n\nYour Stripe statement for February 2026 is now available in your dashboard.\n\nSummary:\n- Total processed: $48,295.00\n- Successful payments: 1,429\n- Refunds: 12 ($847.00)\n- Net volume: $47,448.00\n\nLog in to your Stripe dashboard to view the full report.\n\nThe Stripe Team`, color:'bg-emerald-500', initials:'S' },
    4: { from:'Carol Williams', email:'carol@company.com', subject:'Updated Figma designs for approval', time:'Yesterday', body:`Hi John,\n\nI've updated the Figma file with the new color system and typography. You can view the designs here: [Figma Link]\n\nChanges made:\n- Updated primary color to Indigo-500/Violet-600 gradient\n- New typography scale with Inter font\n- Revised component library with dark mode variants\n- Added new glassmorphism card styles\n\nPlease check and approve so we can move forward with implementation.\n\nCarol`, color:'bg-pink-500', initials:'CW' },
    5: { from:'GitHub', email:'noreply@github.com', subject:'Your repository "liner-admin" has been forked 50 times', time:'Mar 9', body:`Hi John,\n\nCongratulations! Your repository liner-admin has reached 50 forks.\n\nThis means 50 developers are building on top of your work. Thank you for your open source contribution to the community!\n\nHere are your latest stats:\n- ⭐ 342 Stars\n- 🔀 50 Forks\n- 👁️ 1,240 Watchers\n\nKeep up the great work!\n\nGitHub`, color:'bg-slate-700', initials:'G' },
    6: { from:'Emma Davis', email:'emma@company.com', subject:'Bug Report: Dashboard chart not loading on mobile', time:'Mar 8', body:`Hi John,\n\nI found a bug where the revenue chart on the main dashboard doesn't render correctly on mobile devices (iOS and Android).\n\nSteps to reproduce:\n1. Open the dashboard on a mobile device\n2. Scroll to the Revenue Overview chart\n3. The chart appears blank/doesn't render\n\nExpected: Chart should display correctly\nActual: Blank white area\n\nDevice: iPhone 15 Pro (iOS 17.3)\nBrowser: Safari and Chrome\n\nI've attached screenshots. Let me know if you need more info.\n\nEmma`, color:'bg-violet-500', initials:'ED' },
  };

  const list   = document.getElementById('em-list');
  const items  = document.getElementById('em-items');
  const empty  = document.getElementById('em-empty');
  const detail = document.getElementById('em-detail-view');
  const search = document.getElementById('em-search');

  // Folder selection
  document.querySelectorAll('[data-em-folder]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-em-folder]').forEach(b => b.classList.remove('em-folder-active'));
      btn.classList.add('em-folder-active');
      document.getElementById('em-folder-label').textContent = btn.dataset.emFolder.charAt(0).toUpperCase() + btn.dataset.emFolder.slice(1);
      closeDetail();
    });
  });

  // Star toggle
  document.querySelectorAll('[data-em-star]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const icon = btn.querySelector('[data-lucide]');
      const on = icon.classList.contains('em-star-on');
      icon.classList.toggle('em-star-on', !on);
      icon.classList.toggle('em-star-off', on);
    });
  });

  // Search filter
  search?.addEventListener('input', e => {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('.em-item').forEach(it => {
      const id = it.dataset.emId;
      const m = EMAILS[id];
      const match = !q || m.subject.toLowerCase().includes(q) || m.from.toLowerCase().includes(q);
      it.style.display = match ? '' : 'none';
    });
  });

  // Open email
  document.querySelectorAll('.em-item').forEach(it => {
    it.addEventListener('click', () => {
      const id = it.dataset.emId;
      const m = EMAILS[id];
      it.classList.remove('unread');
      it.querySelector('.em-from-unread')?.classList.replace('em-from-unread', 'em-from');
      it.querySelector('.em-subject-unread')?.classList.replace('em-subject-unread', 'em-subject');
      document.querySelectorAll('.em-item').forEach(x => x.classList.remove('em-item-selected'));
      it.classList.add('em-item-selected');
      document.getElementById('em-d-subject').textContent = m.subject;
      const av = document.getElementById('em-d-avatar');
      av.className = 'em-avatar em-avatar-lg ' + m.color;
      av.textContent = m.initials;
      document.getElementById('em-d-from').textContent = m.from;
      document.getElementById('em-d-email').textContent = '<' + m.email + '>';
      document.getElementById('em-d-time').textContent = m.time;
      document.getElementById('em-d-body').textContent = m.body;
      empty.classList.add('hidden');
      detail.classList.remove('hidden');
      list.classList.add('em-list-narrow');
    });
  });

  // Back button
  function closeDetail() {
    detail.classList.add('hidden');
    empty.classList.remove('hidden');
    list.classList.remove('em-list-narrow');
    document.querySelectorAll('.em-item').forEach(x => x.classList.remove('em-item-selected'));
  }
  document.getElementById('em-back')?.addEventListener('click', closeDetail);

  // Compose modal
  const compose = document.getElementById('em-compose');
  document.querySelectorAll('[data-em-open-compose]').forEach(b => b.addEventListener('click', () => compose.classList.remove('hidden')));
  document.querySelectorAll('[data-em-close-compose]').forEach(b => b.addEventListener('click', () => compose.classList.add('hidden')));

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: basic-table ===== */
if (window.LINER_PAGE === 'basic-table.html') {
document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-bt-emp]');
  if (root) {
    const allData = JSON.parse(document.querySelector('[data-bt-data]').textContent);
    const DEPT_COLORS = JSON.parse(document.querySelector('[data-bt-dept-colors]').textContent);
    const STATUS_META = {
      active:   { variant: 'success',   label: 'Active' },
      inactive: { variant: 'secondary', label: 'Inactive' },
      on_leave: { variant: 'warning',   label: 'On Leave' },
    };
    const STATUS_COLORS = {
      success: 'bg-emerald-100 text-emerald-700',
      secondary: 'bg-slate-100 text-slate-600',
      warning: 'bg-amber-100 text-amber-700',
    };
    const STATUS_DOTS = {
      success: 'bg-emerald-500',
      secondary: 'bg-slate-400',
      warning: 'bg-amber-500',
    };
    const PER_PAGE = 10;
    const MAX_SALARY = 180000;
    let search = '';
    let dept = 'All';
    let page = 1;

    const tbody = root.querySelector('[data-bt-tbody]');
    const info = root.querySelector('[data-bt-info]');
    const pager = root.querySelector('[data-bt-pager]');
    const searchInp = root.querySelector('[data-bt-search]');
    const deptSel = root.querySelector('[data-bt-dept]');

    function filtered() {
      const q = search.toLowerCase();
      return allData.filter(e =>
        (dept === 'All' || e.department === dept) &&
        (!q || e.name.toLowerCase().includes(q) || e.email.toLowerCase().includes(q) || e.role.toLowerCase().includes(q) || e.id.toLowerCase().includes(q))
      );
    }

    function render() {
      const data = filtered();
      const totalPages = Math.max(1, Math.ceil(data.length / PER_PAGE));
      const safePage = Math.min(totalPages, page);
      const paged = data.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

      if (paged.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-16 text-slate-400"><svg class="mx-auto mb-2" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:.4;display:block;margin:0 auto 8px"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><p class="text-sm m-0">No employees match your filters</p></td></tr>`;
      } else {
        tbody.innerHTML = paged.map(row => {
          const meta = STATUS_META[row.status] || { variant: 'secondary', label: row.status };
          const pct = Math.round((row.salaryRaw / MAX_SALARY) * 100);
          return `<tr>
            <td><span class="font-mono text-xs font-semibold text-primary-600">${row.id}</span></td>
            <td><div class="flex items-center gap-3"><img src="img/avatars/${row.img}" alt="${row.name}" class="w-8 h-8 rounded-full object-cover"><div><p class="font-semibold text-slate-800 text-sm m-0">${row.name}</p><p class="text-xs text-slate-400 m-0">${row.email}</p></div></div></td>
            <td><span class="bt-dept ${DEPT_COLORS[row.department] || 'bg-slate-100 text-slate-600'}">${row.department}</span></td>
            <td><span class="text-sm text-slate-600">${row.role}</span></td>
            <td><div><span class="text-sm font-semibold text-slate-700">${row.salary}</span><div class="mt-1 h-1 w-20 bg-slate-100 rounded-full overflow-hidden"><div class="h-full rounded-full bg-primary-500" style="width:${pct}%"></div></div></div></td>
            <td><span class="text-xs text-slate-500">${row.joinDate}</span></td>
            <td><span class="bt-badge ${STATUS_COLORS[meta.variant]}"><span class="bt-badge-dot ${STATUS_DOTS[meta.variant]}"></span>${meta.label}</span></td>
            <td><div class="flex items-center gap-1"><button class="bt-action-btn"><i data-lucide="eye" class="lucide-xs"></i></button><button class="bt-action-btn bt-action-cyan"><i data-lucide="edit-2" class="lucide-xs"></i></button><button class="bt-action-btn bt-action-red"><i data-lucide="trash-2" class="lucide-xs"></i></button></div></td>
          </tr>`;
        }).join('');
      }

      info.innerHTML = `Showing <span class="font-semibold text-slate-700">${data.length === 0 ? 0 : (safePage - 1) * PER_PAGE + 1}</span> – <span class="font-semibold text-slate-700">${Math.min(safePage * PER_PAGE, data.length)}</span> of <span class="font-semibold text-slate-700">${data.length}</span> employees`;

      // Pager
      let pagerHtml = `<button class="bt-pager-nav" data-bt-prev ${safePage === 1 ? 'disabled' : ''}><i data-lucide="chevron-left" class="lucide-sm"></i></button>`;
      const shown = Math.min(5, totalPages);
      for (let i = 0; i < shown; i++) {
        let p = i + 1;
        if (totalPages > 5 && safePage > 3) p = safePage - 2 + i;
        if (p > totalPages) continue;
        pagerHtml += `<button class="bt-pager-btn ${safePage === p ? 'bt-pager-active' : ''}" data-bt-page="${p}">${p}</button>`;
      }
      pagerHtml += `<button class="bt-pager-nav" data-bt-next ${safePage === totalPages ? 'disabled' : ''}><i data-lucide="chevron-right" class="lucide-sm"></i></button>`;
      pager.innerHTML = pagerHtml;
      pager.querySelectorAll('[data-bt-page]').forEach(b => b.addEventListener('click', () => { page = +b.dataset.btPage; render(); }));
      pager.querySelector('[data-bt-prev]')?.addEventListener('click', () => { if (page > 1) { page--; render(); } });
      pager.querySelector('[data-bt-next]')?.addEventListener('click', () => { if (page < totalPages) { page++; render(); } });

      window.lucide && lucide.createIcons();
    }

    searchInp.addEventListener('input', () => { search = searchInp.value; page = 1; render(); });
    deptSel.addEventListener('change', () => { dept = deptSel.value; page = 1; render(); });
    render();
  }

  // Selectable: select-all + row checkboxes
  const selAll = document.querySelector('[data-bt-sel-all]');
  if (selAll) {
    const rows = [...document.querySelectorAll('[data-bt-sel-row]')];
    selAll.addEventListener('change', () => rows.forEach(r => r.checked = selAll.checked));
  }

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: enhanced-table ===== */
if (window.LINER_PAGE === 'enhanced-table.html') {
document.addEventListener('DOMContentLoaded', () => {
  // ─── Section 1: Orders Management ─────────────────────────
  const root = document.querySelector('[data-et-orders]');
  if (root) {
    const allOrders = JSON.parse(document.querySelector('[data-et-orders-data]').textContent);
    const STATUS_CONFIG = {
      Delivered:  { icon: 'check-circle', cls: 'bg-emerald-100 text-emerald-500' },
      Processing: { icon: 'clock',         cls: 'bg-cyan-100 text-cyan-500' },
      Shipped:    { icon: 'truck',         cls: 'bg-primary-100 text-primary-500' },
      Cancelled:  { icon: 'x-circle',      cls: 'bg-red-100 text-red-500' },
      Refunded:   { icon: 'refresh-cw',    cls: 'bg-amber-100 text-amber-500' },
    };
    const PER_PAGE = 8;
    let search = '', statusFilter = 'All', sortField = 'id', sortDir = 'asc', page = 1, selected = [];

    const tbody = root.querySelector('[data-et-tbody]');
    const info = root.querySelector('[data-et-info]');
    const pager = root.querySelector('[data-et-pager]');
    const searchInp = root.querySelector('[data-et-search]');
    const statusSel = root.querySelector('[data-et-status]');
    const selAll = root.querySelector('[data-et-sel-all]');
    const bulkBox = root.querySelector('[data-et-bulk]');
    const bulkCount = root.querySelector('[data-et-bulk-count]');

    function filtered() {
      const q = search.toLowerCase();
      return allOrders
        .filter(o => (statusFilter === 'All' || o.status === statusFilter) &&
                     (!q || o.id.toLowerCase().includes(q) || o.customer.toLowerCase().includes(q) || o.email.toLowerCase().includes(q)))
        .sort((a, b) => {
          const va = a[sortField], vb = b[sortField];
          const cmp = typeof va === 'number' ? va - vb : String(va).localeCompare(String(vb));
          return sortDir === 'asc' ? cmp : -cmp;
        });
    }

    function setSortIcons() {
      root.querySelectorAll('[data-et-sort-ic]').forEach(s => {
        const f = s.dataset.etSortIc;
        if (f === sortField) {
          s.innerHTML = `<i data-lucide="${sortDir === 'asc' ? 'chevron-up' : 'chevron-down'}" class="lucide-xxs text-primary-500"></i>`;
        } else {
          s.innerHTML = `<i data-lucide="chevrons-up-down" class="lucide-xxs text-slate-300"></i>`;
        }
      });
    }

    function updateBulk() {
      if (selected.length > 0) {
        bulkBox.classList.remove('hidden');
        bulkBox.classList.add('flex');
        bulkCount.textContent = selected.length + ' selected';
      } else {
        bulkBox.classList.add('hidden');
        bulkBox.classList.remove('flex');
      }
    }

    function render() {
      const data = filtered();
      const totalPages = Math.max(1, Math.ceil(data.length / PER_PAGE));
      const safePage = Math.min(totalPages, page);
      const paged = data.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

      if (paged.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-12 text-slate-400"><svg class="mx-auto mb-2" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:.4;display:block;margin:0 auto 8px"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><p class="text-sm m-0">No orders found</p></td></tr>`;
      } else {
        tbody.innerHTML = paged.map(order => {
          const cfg = STATUS_CONFIG[order.status];
          const isSel = selected.includes(order.id);
          return `<tr class="${isSel ? 'bg-primary-50' : ''}">
            <td><input type="checkbox" class="bt-cb" data-et-row="${order.id}" ${isSel ? 'checked' : ''}></td>
            <td><span class="font-mono text-xs font-semibold text-primary-600">${order.id}</span></td>
            <td><div class="flex items-center gap-2-5"><img src="img/avatars/${order.img}" alt="${order.customer}" class="w-8 h-8 rounded-full object-cover"><div><p class="text-sm font-semibold text-slate-800 m-0">${order.customer}</p><p class="text-xs text-slate-400 m-0">${order.email}</p></div></div></td>
            <td><span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-xs font-bold text-slate-700">${order.items}</span></td>
            <td><span class="text-sm font-bold text-slate-800">$${order.amount.toFixed(2)}</span></td>
            <td><span class="text-xs text-slate-500">${order.method}</span></td>
            <td><span class="bt-status-pill ${cfg.cls}"><i data-lucide="${cfg.icon}" class="lucide-xxs"></i>${order.status}</span></td>
            <td><span class="text-xs text-slate-500">${order.date}</span></td>
            <td><div class="flex items-center gap-1"><button class="bt-action-btn"><i data-lucide="eye" class="lucide-xs"></i></button><button class="bt-action-btn bt-action-cyan"><i data-lucide="edit-2" class="lucide-xs"></i></button><button class="bt-action-btn"><i data-lucide="more-horizontal" class="lucide-xs"></i></button></div></td>
          </tr>`;
        }).join('');
      }

      // Bind row checkboxes
      tbody.querySelectorAll('[data-et-row]').forEach(cb => {
        cb.addEventListener('change', () => {
          const id = cb.dataset.etRow;
          if (cb.checked) selected.push(id);
          else selected = selected.filter(x => x !== id);
          updateBulk();
        });
      });

      info.innerHTML = `${data.length === 0 ? '0' : (safePage - 1) * PER_PAGE + 1}–${Math.min(safePage * PER_PAGE, data.length)} of <span class="font-semibold text-slate-700">${data.length}</span> orders`;

      let pagerHtml = `<button class="bt-pager-nav" data-et-prev ${safePage === 1 ? 'disabled' : ''}><i data-lucide="chevron-left" class="lucide-sm"></i></button>`;
      const shown = Math.min(5, totalPages);
      for (let i = 0; i < shown; i++) {
        let p = i + 1;
        if (totalPages > 5 && safePage > 3) p = safePage - 2 + i;
        if (p > totalPages) continue;
        pagerHtml += `<button class="bt-pager-btn ${safePage === p ? 'bt-pager-active' : ''}" data-et-page="${p}">${p}</button>`;
      }
      pagerHtml += `<button class="bt-pager-nav" data-et-next ${safePage === totalPages ? 'disabled' : ''}><i data-lucide="chevron-right" class="lucide-sm"></i></button>`;
      pager.innerHTML = pagerHtml;
      pager.querySelectorAll('[data-et-page]').forEach(b => b.addEventListener('click', () => { page = +b.dataset.etPage; render(); }));
      pager.querySelector('[data-et-prev]')?.addEventListener('click', () => { if (page > 1) { page--; render(); } });
      pager.querySelector('[data-et-next]')?.addEventListener('click', () => { if (page < totalPages) { page++; render(); } });

      setSortIcons();
      window.lucide && lucide.createIcons();
    }

    // Sort headers
    root.querySelectorAll('[data-et-sort]').forEach(th => {
      th.addEventListener('click', () => {
        const f = th.dataset.etSort;
        if (sortField === f) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
        else { sortField = f; sortDir = 'asc'; }
        page = 1;
        render();
      });
    });

    searchInp.addEventListener('input', () => { search = searchInp.value; page = 1; render(); });
    statusSel.addEventListener('change', () => { statusFilter = statusSel.value; page = 1; render(); });
    selAll.addEventListener('change', () => {
      const rows = tbody.querySelectorAll('[data-et-row]');
      if (selAll.checked) {
        rows.forEach(r => { r.checked = true; if (!selected.includes(r.dataset.etRow)) selected.push(r.dataset.etRow); });
      } else {
        rows.forEach(r => { r.checked = false; selected = selected.filter(x => x !== r.dataset.etRow); });
      }
      updateBulk();
    });
    render();
  }

  // ─── Section 2: Expandable rows ───────────────────────────
  document.querySelectorAll('[data-et-exp]').forEach(row => {
    row.addEventListener('click', () => {
      const id = row.dataset.etExp;
      const pane = document.querySelector(`[data-et-exp-pane="${id}"]`);
      const isOpen = row.classList.toggle('et-exp-open');
      pane.classList.toggle('hidden', !isOpen);
    });
  });

  // ─── Section 4: File star toggle ──────────────────────────
  document.querySelectorAll('[data-et-star]').forEach(b => {
    b.addEventListener('click', (e) => { e.stopPropagation(); b.classList.toggle('et-star-on'); });
  });

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: error ===== */
if (window.LINER_PAGE === 'error.html') {
document.addEventListener('DOMContentLoaded', () => {
  const r = document.getElementById('retry-btn');
  if (r) r.addEventListener('click', () => location.reload());
  window.lucide && lucide.createIcons();
});

}

/* ===== Page: forgot-password ===== */
if (window.LINER_PAGE === 'forgot-password.html') {
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('forgot-form');
  const emailInput = document.getElementById('forgot-email');
  const view = document.getElementById('forgot-view');
  const sent = document.getElementById('forgot-sent');
  const shown = document.getElementById('forgot-shown-email');
  const btnText = document.getElementById('forgot-btn-text');
  const tryAgain = document.getElementById('forgot-try-again');

  if (form) form.addEventListener('submit', e => {
    e.preventDefault();
    btnText.textContent = 'Sending...';
    setTimeout(() => {
      btnText.textContent = 'Send Reset Link';
      shown.textContent = emailInput.value;
      view.classList.add('d-none');
      sent.classList.remove('d-none');
      window.lucide && lucide.createIcons();
    }, 800);
  });
  if (tryAgain) tryAgain.addEventListener('click', () => {
    sent.classList.add('d-none');
    view.classList.remove('d-none');
    window.lucide && lucide.createIcons();
  });

  window.lucide && lucide.createIcons();
});

}

/* ===== Shared form helpers ===== */
function _liner_toggle_init() {
  document.querySelectorAll('[data-fe-toggle]').forEach(t => {
    if (t.dataset.feToggleBound) return;
    t.dataset.feToggleBound = '1';
    t.addEventListener('click', () => t.classList.toggle('fe-toggle-on'));
  });
}

/* ===== Page: form-elements ===== */
if (window.LINER_PAGE === 'form-elements.html') {
document.addEventListener('DOMContentLoaded', () => {
  // Password eye toggle
  document.querySelectorAll('[data-fe-pw]').forEach(wrap => {
    const inp = wrap.querySelector('[data-fe-pw-input]');
    const btn = wrap.querySelector('[data-fe-pw-toggle]');
    btn.addEventListener('click', () => {
      inp.type = inp.type === 'password' ? 'text' : 'password';
      btn.innerHTML = `<i data-lucide="${inp.type === 'password' ? 'eye' : 'eye-off'}" class="lucide-xs"></i>`;
      window.lucide && lucide.createIcons();
    });
  });

  // Character counter
  const cc = document.querySelector('[data-fe-cc]');
  if (cc) {
    const out = document.querySelector('[data-fe-cc-count]');
    const err = document.querySelector('[data-fe-cc-err]');
    cc.addEventListener('input', () => {
      const len = cc.value.length;
      out.textContent = len + '/80';
      out.className = 'text-xs font-medium ' + (len > 80 ? 'text-red-500' : 'text-slate-400');
      cc.classList.toggle('fe-input-error', len > 80);
      err.classList.toggle('hidden', len <= 80);
    });
  }

  // Textarea counter
  const ta = document.querySelector('[data-fe-ta]');
  if (ta) {
    const out = document.querySelector('[data-fe-ta-count]');
    const update = () => {
      const len = ta.value.length;
      out.textContent = len + '/300';
      out.className = 'text-xs font-medium ' + (len > 300 ? 'text-red-500' : 'text-slate-400');
      ta.classList.toggle('fe-input-error', len > 300);
    };
    ta.addEventListener('input', update);
    update();
  }

  // Select message
  const sel = document.querySelector('[data-fe-sel]');
  if (sel) {
    const msg = document.querySelector('[data-fe-sel-msg]');
    sel.addEventListener('change', () => {
      if (sel.value) { msg.classList.remove('hidden'); msg.innerHTML = 'You selected: <strong>' + sel.value + '</strong>'; }
      else msg.classList.add('hidden');
    });
  }

  // Checkboxes
  document.querySelectorAll('[data-fe-cb]').forEach(cb => {
    cb.addEventListener('click', () => cb.classList.toggle('fe-cb-on'));
  });

  // Card-style checkbox (clicks card toggles inner cb + card highlight)
  document.querySelectorAll('[data-fe-card-cb]').forEach(card => {
    card.addEventListener('click', (e) => {
      if (e.target.closest('[data-fe-cb]')) return;
      card.classList.toggle('fe-card-cb-on');
      card.querySelector('[data-fe-cb]')?.classList.toggle('fe-cb-on');
    });
  });

  // Radio groups
  document.querySelectorAll('[data-fe-radio-group]').forEach(group => {
    group.querySelectorAll('[data-fe-radio]').forEach(opt => {
      opt.addEventListener('click', () => {
        group.querySelectorAll('[data-fe-radio]').forEach(o => {
          o.classList.remove('fe-radio-card-on');
          o.querySelector('.fe-radio')?.classList.remove('fe-radio-on');
        });
        opt.classList.add('fe-radio-card-on');
        opt.querySelector('.fe-radio')?.classList.add('fe-radio-on');
      });
    });
  });

  // Toggles
  _liner_toggle_init();

  // Range
  document.querySelectorAll('[data-fe-range]').forEach(r => {
    const out = document.querySelector('[data-fe-range-out="' + r.dataset.feRange + '"]');
    const target = r.dataset.feRangeTarget && document.getElementById(r.dataset.feRangeTarget);
    r.addEventListener('input', () => {
      if (out) out.textContent = r.value + '%';
      if (target) target.style.opacity = (r.value / 100).toString();
    });
  });

  // File input
  const file = document.querySelector('[data-fe-file]');
  if (file) {
    const name = document.querySelector('[data-fe-file-name]');
    file.addEventListener('change', () => {
      name.textContent = file.files[0] ? file.files[0].name : 'No file chosen';
    });
  }

  // Dropzone
  const dz = document.querySelector('[data-fe-dropzone]');
  if (dz) {
    const dzInput = dz.querySelector('[data-fe-dz-input]');
    const empty = dz.querySelector('[data-fe-dz-empty]');
    const filled = dz.querySelector('[data-fe-dz-filled]');
    const nameEl = dz.querySelector('[data-fe-dz-name]');
    const remove = dz.querySelector('[data-fe-dz-remove]');
    const txt = dz.querySelector('[data-fe-dz-text]');
    const setFile = (f) => {
      if (f) {
        empty.classList.add('hidden');
        filled.classList.remove('hidden');
        nameEl.textContent = f.name;
      } else {
        empty.classList.remove('hidden');
        filled.classList.add('hidden');
      }
    };
    dz.addEventListener('click', (e) => { if (e.target.closest('[data-fe-dz-remove]')) return; dzInput.click(); });
    dz.addEventListener('dragover', (e) => { e.preventDefault(); dz.classList.add('fe-dropzone-active'); if (txt) txt.textContent = 'Drop your file here'; });
    dz.addEventListener('dragleave', () => { dz.classList.remove('fe-dropzone-active'); if (txt) txt.textContent = 'Drop files here or click to browse'; });
    dz.addEventListener('drop', (e) => { e.preventDefault(); dz.classList.remove('fe-dropzone-active'); if (txt) txt.textContent = 'Drop files here or click to browse'; if (e.dataTransfer.files[0]) setFile(e.dataTransfer.files[0]); });
    dzInput.addEventListener('change', () => { if (dzInput.files[0]) setFile(dzInput.files[0]); });
    remove.addEventListener('click', (e) => { e.stopPropagation(); setFile(null); dzInput.value = ''; });
  }

  // Color picker
  const colorGroup = document.querySelector('[data-fe-color-group]');
  if (colorGroup) {
    const color = colorGroup.querySelector('[data-fe-color]');
    const text = colorGroup.querySelector('[data-fe-color-text]');
    const preview = colorGroup.querySelector('[data-fe-color-preview]');
    const out = colorGroup.querySelector('[data-fe-color-out]');
    const setColor = (val) => {
      color.value = val;
      text.value = val;
      preview.style.backgroundColor = val;
      out.textContent = val;
      colorGroup.querySelectorAll('[data-fe-swatch]').forEach(s => s.classList.toggle('fe-swatch-on', s.dataset.feSwatch.toLowerCase() === val.toLowerCase()));
    };
    color.addEventListener('input', () => setColor(color.value));
    text.addEventListener('input', () => setColor(text.value));
    colorGroup.querySelectorAll('[data-fe-swatch]').forEach(s => s.addEventListener('click', () => setColor(s.dataset.feSwatch)));
  }

  // Tag input
  const tags = document.querySelector('[data-fe-tags]');
  if (tags) {
    const list = tags.querySelector('.fe-tags-list');
    const inp = tags.querySelector('[data-fe-tag-input]');
    const refreshHandlers = () => {
      list.querySelectorAll('[data-fe-tag-rm]').forEach(rm => {
        if (rm.dataset.bound) return;
        rm.dataset.bound = '1';
        rm.addEventListener('click', () => rm.closest('[data-fe-tag]').remove());
      });
    };
    refreshHandlers();
    inp.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && inp.value.trim()) {
        e.preventDefault();
        const v = inp.value.trim();
        if ([...list.querySelectorAll('[data-fe-tag]')].some(t => t.textContent.replace('×','').trim() === v)) { inp.value = ''; return; }
        const span = document.createElement('span');
        span.className = 'fe-tag';
        span.dataset.feTag = '';
        span.innerHTML = v + '<button type="button" class="fe-tag-x" data-fe-tag-rm><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
        list.appendChild(span);
        inp.value = '';
        refreshHandlers();
      }
    });
  }

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: form-layouts ===== */
if (window.LINER_PAGE === 'form-layouts.html') {
document.addEventListener('DOMContentLoaded', () => {
  // Avatar upload
  const avatar = document.querySelector('[data-fl-avatar]');
  if (avatar) {
    const input = avatar.querySelector('[data-fl-avatar-input]');
    const img = avatar.querySelector('.fl-avatar-img');
    const empty = avatar.querySelector('.fl-avatar-empty');
    document.querySelectorAll('[data-fl-avatar-btn]').forEach(b => b.addEventListener('click', () => input.click()));
    input.addEventListener('change', () => {
      const f = input.files[0];
      if (f) { img.src = URL.createObjectURL(f); img.classList.remove('hidden'); empty.classList.add('hidden'); }
    });
  }

  // Priority radio
  document.querySelectorAll('[data-fl-priority]').forEach(g => {
    g.querySelectorAll('[data-fl-priority-v]').forEach(opt => {
      opt.addEventListener('click', () => {
        g.querySelectorAll('[data-fl-priority-v]').forEach(o => {
          o.classList.remove('fl-priority-on','fl-priority-emerald','fl-priority-amber','fl-priority-orange','fl-priority-red');
          o.classList.add('fl-priority-off');
        });
        opt.classList.remove('fl-priority-off');
        opt.classList.add('fl-priority-on', 'fl-priority-' + opt.dataset.flPriorityC);
      });
    });
  });

  // Message counter
  const msg = document.querySelector('[data-fl-msg]');
  if (msg) {
    const count = document.querySelector('[data-fl-msg-count]');
    const bar = document.querySelector('[data-fl-msg-bar]');
    msg.addEventListener('input', () => {
      const len = msg.value.length;
      count.textContent = len + '/500';
      count.className = 'text-xs font-medium ' + (len > 450 ? 'text-red-500' : 'text-slate-400');
      bar.style.width = (len / 500 * 100) + '%';
      bar.className = 'h-full rounded-full ' + (len > 450 ? 'bg-red-500' : 'bg-primary-500');
    });
  }

  // Card preview live
  const cardInput = document.querySelector('[data-fl-card-input]');
  if (cardInput) {
    const display = document.querySelector('[data-fl-card-num]');
    cardInput.addEventListener('input', () => {
      const digits = cardInput.value.replace(/\D/g, '').slice(0, 16);
      cardInput.value = digits.replace(/(.{4})/g, '$1 ').trim();
      display.textContent = cardInput.value || '•••• •••• •••• ••••';
    });
  }

  // Toggles
  _liner_toggle_init();

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: form-horizontal ===== */
if (window.LINER_PAGE === 'form-horizontal.html') {
document.addEventListener('DOMContentLoaded', () => {
  _liner_toggle_init();

  // Service status text
  const sv = document.querySelector('[data-fh-service]');
  if (sv) {
    const txt = document.querySelector('[data-fh-service-text]');
    sv.addEventListener('click', () => {
      setTimeout(() => {
        const on = sv.classList.contains('fe-toggle-on');
        txt.textContent = on ? 'Service is active' : 'Service is paused';
        txt.className = 'text-sm font-medium ' + (on ? 'text-emerald-600' : 'text-slate-500');
      }, 10);
    });
  }

  // Password form
  const pwForm = document.querySelector('[data-fh-pw-form]');
  if (pwForm) {
    document.querySelectorAll('[data-fh-pw-eye]').forEach(btn => {
      btn.addEventListener('click', () => {
        const which = btn.dataset.fhPwEye;
        const inp = document.querySelector('[data-fh-pw-' + which + ']');
        inp.type = inp.type === 'password' ? 'text' : 'password';
        btn.innerHTML = `<i data-lucide="${inp.type === 'password' ? 'eye' : 'eye-off'}" class="lucide-xs"></i>`;
        window.lucide && lucide.createIcons();
      });
    });

    const newPw = document.querySelector('[data-fh-pw-new]');
    const confirmPw = document.querySelector('[data-fh-pw-confirm]');
    const strengthBox = document.querySelector('[data-fh-pw-strength]');
    const barsBox = document.querySelector('[data-fh-pw-bars]');
    const label = document.querySelector('[data-fh-pw-label]');
    const matchEl = document.querySelector('[data-fh-pw-match]');
    const colors = ['', 'bg-red-500', 'bg-amber-400', 'bg-blue-500', 'bg-emerald-500'];
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const textColors = ['', 'text-red-500', 'text-amber-500', 'text-blue-500', 'text-emerald-500'];

    const updateStrength = () => {
      const v = newPw.value;
      if (!v) { strengthBox.classList.add('hidden'); return; }
      strengthBox.classList.remove('hidden');
      let s = 0;
      if (v.length >= 8) s++;
      if (v.length >= 12) s++;
      if (/[A-Z]/.test(v)) s++;
      if (/[0-9]/.test(v)) s++;
      if (/[^A-Za-z0-9]/.test(v)) s++;
      s = Math.min(s, 4);
      barsBox.querySelectorAll('div').forEach((b, i) => {
        b.className = 'h-1-5 flex-1 rounded-full ' + ((i + 1) <= s ? colors[s] : 'bg-slate-200');
      });
      label.textContent = labels[s] + ' password';
      label.className = 'text-xs font-medium ' + textColors[s];

      // Requirements
      const reqs = { len: v.length >= 8, upper: /[A-Z]/.test(v), num: /[0-9]/.test(v), sym: /[^A-Za-z0-9]/.test(v) };
      Object.keys(reqs).forEach(k => {
        const el = document.querySelector('[data-fh-req="' + k + '"]');
        if (el) el.classList.toggle('fh-req-active', reqs[k]);
      });
    };

    const updateMatch = () => {
      if (!newPw.value || !confirmPw.value) { matchEl.classList.add('hidden'); return; }
      matchEl.classList.remove('hidden');
      if (newPw.value === confirmPw.value) { matchEl.textContent = 'Passwords match'; matchEl.className = 'text-xs mt-1-5 text-emerald-600'; }
      else { matchEl.textContent = 'Passwords do not match'; matchEl.className = 'text-xs mt-1-5 text-red-500'; }
    };

    newPw.addEventListener('input', () => { updateStrength(); updateMatch(); });
    confirmPw.addEventListener('input', updateMatch);
  }

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: form-validation ===== */
if (window.LINER_PAGE === 'form-validation.html') {
document.addEventListener('DOMContentLoaded', () => {
  // ─── 1. Live validation form ──────────────────────────────
  const live = document.querySelector('[data-fv-live]');
  if (live) {
    const validators = {
      email: (v) => !v ? 'Email is required' : !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? 'Enter a valid email address' : null,
      password: (v) => !v ? 'Password is required' : v.length < 8 ? 'Password must be at least 8 characters' : null,
      confirm: (v) => !v ? 'Please confirm your password' : v !== live.querySelector('[name="password"]').value ? 'Passwords do not match' : null,
      phone: (v) => !v ? 'Phone number is required' : !/^\+?[\d\s\-()]{7,}$/.test(v) ? 'Enter numbers only (e.g. +1 555 000 0000)' : null,
      url: (v) => v && !v.startsWith('https://') ? 'URL must start with https://' : null,
    };

    const touched = {};

    const paintField = (name) => {
      const field = live.querySelector('[data-fv-field="' + name + '"]');
      const input = field.querySelector('[name="' + name + '"]');
      const status = field.querySelector('[data-fv-status]');
      const msg = field.querySelector('[data-fv-msg]');
      const err = validators[name](input.value);
      input.classList.remove('fe-input-error', 'fe-input-success');
      if (status) status.classList.add('hidden');
      if (touched[name]) {
        if (err) {
          input.classList.add('fe-input-error');
          msg.textContent = err;
          msg.className = 'flex items-center gap-1-5 text-xs text-red-600 mt-1-5';
          msg.classList.remove('hidden');
        } else if (input.value) {
          input.classList.add('fe-input-success');
          if (status) status.classList.remove('hidden');
          if (name === 'email' || name === 'confirm') {
            msg.textContent = name === 'email' ? 'Valid email format' : 'Passwords match!';
            msg.className = 'flex items-center gap-1-5 text-xs text-emerald-600 mt-1-5';
            msg.classList.remove('hidden');
          } else if (name === 'phone' || name === 'url') {
            msg.textContent = name === 'phone' ? 'Valid phone number' : 'Valid URL';
            msg.className = 'flex items-center gap-1-5 text-xs text-emerald-600 mt-1-5';
            msg.classList.remove('hidden');
          } else {
            msg.classList.add('hidden');
          }
        }
      }
    };

    live.querySelectorAll('input').forEach(inp => {
      inp.addEventListener('blur', () => { touched[inp.name] = true; paintField(inp.name); });
      inp.addEventListener('input', () => { if (touched[inp.name]) paintField(inp.name); });
    });

    // Password strength
    const pw = live.querySelector('[name="password"]');
    const strengthBox = live.querySelector('[data-fv-pw-strength]');
    const barsBox = live.querySelector('[data-fv-pw-bars]');
    const pwLabel = live.querySelector('[data-fv-pw-label]');
    const colors = ['', 'bg-red-500', 'bg-amber-400', 'bg-blue-500', 'bg-emerald-500'];
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const textColors = ['', 'text-red-500', 'text-amber-500', 'text-blue-500', 'text-emerald-500'];
    pw.addEventListener('input', () => {
      const v = pw.value;
      if (!v) { strengthBox.classList.add('hidden'); return; }
      strengthBox.classList.remove('hidden');
      let s = 0;
      if (v.length >= 8) s++; if (v.length >= 12) s++; if (/[A-Z]/.test(v)) s++; if (/[0-9]/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
      s = Math.min(s, 4);
      barsBox.querySelectorAll('div').forEach((b, i) => b.className = 'h-1-5 flex-1 rounded-full ' + ((i + 1) <= s ? colors[s] : 'bg-slate-200'));
      pwLabel.textContent = labels[s] + ' password';
      pwLabel.className = 'text-xs font-medium ' + textColors[s];
    });

    // Eye toggle
    const eyeBtn = live.querySelector('[data-fv-eye]');
    if (eyeBtn) eyeBtn.addEventListener('click', () => {
      pw.type = pw.type === 'password' ? 'text' : 'password';
      eyeBtn.innerHTML = `<i data-lucide="${pw.type === 'password' ? 'eye' : 'eye-off'}" class="lucide-xs"></i>`;
      window.lucide && lucide.createIcons();
    });

    live.addEventListener('submit', (e) => {
      e.preventDefault();
      ['email','password','confirm','phone','url'].forEach(n => { touched[n] = true; paintField(n); });
    });
  }

  // ─── 2. Required fields demo ──────────────────────────────
  const reqForm = document.querySelector('[data-fv-required]');
  if (reqForm) {
    let submitted = false;
    const fields = ['username','email','department','startDate'];
    const summary = document.querySelector('[data-fv-req-summary]');

    const paint = () => {
      let anyEmpty = false;
      fields.forEach(name => {
        const wrap = reqForm.querySelector('[data-fv-req-field="' + name + '"]');
        const inp = wrap.querySelector('[name="' + name + '"]');
        const msg = wrap.querySelector('[data-fv-req-msg]');
        const empty = submitted && !inp.value;
        inp.classList.toggle('fe-input-error', empty);
        msg.classList.toggle('hidden', !empty);
        if (empty) anyEmpty = true;
      });
      summary.classList.toggle('hidden', !anyEmpty);
    };

    reqForm.addEventListener('submit', (e) => { e.preventDefault(); submitted = true; paint(); });
    fields.forEach(name => {
      reqForm.querySelector('[name="' + name + '"]').addEventListener('input', paint);
      reqForm.querySelector('[name="' + name + '"]').addEventListener('change', paint);
    });
    reqForm.querySelector('[data-fv-req-reset]').addEventListener('click', () => {
      submitted = false;
      fields.forEach(n => { reqForm.querySelector('[name="' + n + '"]').value = ''; });
      paint();
    });
  }

  // ─── 3. Pattern / Mask Validation ──────────────────────────
  const cardField = document.querySelector('[data-fv-card]');
  if (cardField) {
    const count = document.querySelector('[data-fv-card-count]');
    const ok = document.querySelector('[data-fv-card-success]');
    cardField.addEventListener('input', () => {
      const d = cardField.value.replace(/\D/g, '').slice(0, 16);
      cardField.value = d.replace(/(.{4})/g, '$1-').replace(/-$/, '');
      const n = d.length;
      count.textContent = n + '/16';
      if (n === 16) { cardField.classList.add('fe-input-success'); cardField.classList.remove('fe-input-error'); ok.classList.remove('hidden'); }
      else if (n > 0) { cardField.classList.add('fe-input-error'); cardField.classList.remove('fe-input-success'); ok.classList.add('hidden'); }
      else { cardField.classList.remove('fe-input-error','fe-input-success'); ok.classList.add('hidden'); }
    });
  }
  const phoneMask = document.querySelector('[data-fv-phone]');
  if (phoneMask) {
    const count = document.querySelector('[data-fv-phone-count]');
    const ok = document.querySelector('[data-fv-phone-success]');
    phoneMask.addEventListener('input', () => {
      const d = phoneMask.value.replace(/\D/g, '').slice(0, 10);
      let v = d;
      if (d.length > 3 && d.length <= 6) v = '(' + d.slice(0,3) + ') ' + d.slice(3);
      else if (d.length > 6) v = '(' + d.slice(0,3) + ') ' + d.slice(3,6) + '-' + d.slice(6);
      phoneMask.value = v;
      count.textContent = d.length + '/10';
      if (d.length === 10) { phoneMask.classList.add('fe-input-success'); phoneMask.classList.remove('fe-input-error'); ok.classList.remove('hidden'); }
      else if (d.length > 0) { phoneMask.classList.add('fe-input-error'); phoneMask.classList.remove('fe-input-success'); ok.classList.add('hidden'); }
      else { phoneMask.classList.remove('fe-input-error','fe-input-success'); ok.classList.add('hidden'); }
    });
  }
  const ssn = document.querySelector('[data-fv-ssn]');
  if (ssn) ssn.addEventListener('input', () => {
    const d = ssn.value.replace(/\D/g, '').slice(0, 9);
    let v = d;
    if (d.length > 3 && d.length <= 5) v = d.slice(0,3) + '-' + d.slice(3);
    else if (d.length > 5) v = d.slice(0,3) + '-' + d.slice(3,5) + '-' + d.slice(5);
    ssn.value = v;
  });
  const zip = document.querySelector('[data-fv-zip]');
  if (zip) zip.addEventListener('input', () => { zip.value = zip.value.replace(/\D/g, '').slice(0, 5); });

  // ─── 4. Multi-step wizard ─────────────────────────────────
  const wiz = document.querySelector('[data-fv-wizard]');
  if (wiz) {
    let step = 0;
    const data = { firstName:'', lastName:'', email:'', username:'', password:'', role:'', terms:false };
    const inputs = wiz.querySelectorAll('[data-fv-w-input]');
    inputs.forEach(inp => {
      inp.addEventListener('input', () => {
        const key = inp.dataset.fvWInput;
        data[key] = inp.type === 'checkbox' ? inp.checked : inp.value;
        canNextUpdate();
      });
      inp.addEventListener('change', () => {
        const key = inp.dataset.fvWInput;
        data[key] = inp.type === 'checkbox' ? inp.checked : inp.value;
        canNextUpdate();
      });
    });

    const canNext = () => {
      if (step === 0) return data.firstName && data.lastName && data.email;
      if (step === 1) return data.username && data.password && data.role;
      return data.terms;
    };

    const counter = wiz.querySelector('[data-fv-w-counter]');
    const labels = ['Personal Info','Account Setup','Confirmation'];
    const stepIcons = ['user','lock','check-circle'];

    const render = () => {
      wiz.querySelectorAll('[data-fv-w-pane]').forEach(p => p.classList.toggle('hidden', +p.dataset.fvWPane !== step));
      counter.textContent = 'Step ' + (step + 1) + ' of 3 — ' + labels[step];
      // Steps
      wiz.querySelectorAll('[data-fv-w-step]').forEach(el => {
        const i = +el.dataset.fvWStep;
        el.className = 'w-9 h-9 rounded-full flex items-center justify-center font-semibold text-sm fv-step ' + (i < step ? 'fv-step-done' : i === step ? 'fv-step-current' : 'fv-step-future');
        el.innerHTML = `<i data-lucide="${i < step ? 'check-circle' : stepIcons[i]}" class="lucide-xs"></i>`;
      });
      wiz.querySelectorAll('[data-fv-w-step-label]').forEach(el => {
        const i = +el.dataset.fvWStepLabel;
        el.className = 'text-xs font-medium hidden-sm-block fv-step-label' + (i === step ? ' fv-step-label-current' : i < step ? ' fv-step-label-done' : '');
        if (i === step) el.style.color = 'var(--primary-600, #2563eb)';
        else if (i < step) el.style.color = '#059669';
        else el.style.color = '#94a3b8';
      });
      wiz.querySelectorAll('[data-fv-w-line]').forEach(el => {
        const i = +el.dataset.fvWLine;
        el.className = 'flex-1 h-0-5 mx-2 mb-5 rounded-full fv-step-line' + (i < step ? ' fv-step-line-done' : ' bg-slate-200');
      });
      // Dots
      const dots = wiz.querySelector('[data-fv-w-dots]');
      dots.innerHTML = ['','',''].map((_,i) => '<div class="h-1-5 rounded-full ' + (i === step ? 'bg-primary-500' : i < step ? 'bg-emerald-400' : 'bg-slate-200') + '" style="width:' + (i === step ? '24px' : '12px') + '"></div>').join('');
      // Navigation visibility
      wiz.querySelector('[data-fv-w-back]').disabled = step === 0;
      const next = wiz.querySelector('[data-fv-w-next]');
      const finish = wiz.querySelector('[data-fv-w-finish]');
      if (step < 2) { next.classList.remove('hidden'); finish.classList.add('hidden'); next.disabled = !canNext(); }
      else { next.classList.add('hidden'); finish.classList.remove('hidden'); finish.disabled = !canNext(); }
      // Output for review
      if (step === 2) {
        const out = (k, v) => { const el = wiz.querySelector('[data-fv-w-out="' + k + '"]'); if (el) el.textContent = v; };
        out('fullName', (data.firstName + ' ' + data.lastName).trim() || '—');
        out('email', data.email || '—');
        out('usernameAt', '@' + (data.username || ''));
        out('role', data.role || '—');
        out('password', '••••••••');
      }
      window.lucide && lucide.createIcons();
    };
    const canNextUpdate = () => {
      const next = wiz.querySelector('[data-fv-w-next]');
      const finish = wiz.querySelector('[data-fv-w-finish]');
      if (step < 2) next.disabled = !canNext();
      else finish.disabled = !canNext();
    };

    // Password strength in wizard
    const wPw = wiz.querySelector('[data-fv-w-pw]');
    if (wPw) {
      const strengthBox = wiz.querySelector('[data-fv-w-strength]');
      const barsBox = wiz.querySelector('[data-fv-w-pw-bars]');
      const pwLabel = wiz.querySelector('[data-fv-w-pw-label]');
      const colors = ['', 'bg-red-500', 'bg-amber-400', 'bg-blue-500', 'bg-emerald-500'];
      const labels2 = ['', 'Weak', 'Fair', 'Good', 'Strong'];
      const textColors = ['', 'text-red-500', 'text-amber-500', 'text-blue-500', 'text-emerald-500'];
      wPw.addEventListener('input', () => {
        const v = wPw.value;
        if (!v) { strengthBox.classList.add('hidden'); return; }
        strengthBox.classList.remove('hidden');
        let s = 0;
        if (v.length >= 8) s++; if (v.length >= 12) s++; if (/[A-Z]/.test(v)) s++; if (/[0-9]/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
        s = Math.min(s, 4);
        barsBox.querySelectorAll('div').forEach((b, i) => b.className = 'h-1-5 flex-1 rounded-full ' + ((i + 1) <= s ? colors[s] : 'bg-slate-200'));
        pwLabel.textContent = labels2[s] + ' password';
        pwLabel.className = 'text-xs font-medium ' + textColors[s];
      });
    }

    wiz.querySelector('[data-fv-w-next]').addEventListener('click', () => { if (canNext()) { step++; render(); } });
    wiz.querySelector('[data-fv-w-back]').addEventListener('click', () => { if (step > 0) { step--; render(); } });
    wiz.querySelector('[data-fv-w-finish]').addEventListener('click', () => {
      if (!canNext()) return;
      wiz.querySelectorAll('[data-fv-w-pane], .border-b, .border-t').forEach(el => {});
      // hide all panes + nav
      [...wiz.children].forEach(c => { if (!c.hasAttribute('data-fv-w-success')) c.classList.add('hidden'); });
      const success = wiz.querySelector('[data-fv-w-success]');
      success.classList.remove('hidden');
      wiz.querySelector('[data-fv-w-success-name]').textContent = data.firstName;
      wiz.querySelector('[data-fv-w-success-user]').textContent = '@' + data.username;
      wiz.querySelector('[data-fv-w-success-email]').textContent = data.email;
    });
    wiz.querySelector('[data-fv-w-reset]').addEventListener('click', () => {
      step = 0;
      Object.keys(data).forEach(k => { data[k] = (k === 'terms' ? false : ''); });
      inputs.forEach(inp => { if (inp.type === 'checkbox') inp.checked = false; else inp.value = ''; });
      [...wiz.children].forEach(c => c.classList.remove('hidden'));
      wiz.querySelector('[data-fv-w-success]').classList.add('hidden');
      render();
    });
    render();
  }

  // ─── 5. Login Form Validation ─────────────────────────────
  const login = document.querySelector('[data-fv-login]');
  if (login) {
    const form = login.querySelector('[data-fv-login-form-el]');
    const validate = (name, value) => {
      if (name === 'email') {
        if (!value) return 'Email is required';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Enter a valid email address';
      }
      if (name === 'password') {
        if (!value) return 'Password is required';
        if (value.length < 6) return 'Password must be at least 6 characters';
      }
      return null;
    };

    const touched = {};
    const paint = (name) => {
      const field = login.querySelector('[data-fv-login-field="' + name + '"]');
      const input = field.querySelector('[name="' + name + '"]');
      const status = field.querySelector('[data-fv-login-status]');
      const msg = field.querySelector('[data-fv-login-msg]');
      const err = validate(name, input.value);
      input.classList.remove('fe-input-error','fe-input-success');
      if (status) status.classList.add('hidden');
      msg.classList.add('hidden');
      if (touched[name]) {
        if (err) {
          input.classList.add('fe-input-error');
          msg.textContent = err;
          msg.className = 'flex items-center gap-1-5 text-xs text-red-600 mt-1-5';
          msg.classList.remove('hidden');
        } else if (input.value) {
          input.classList.add('fe-input-success');
          if (status) status.classList.remove('hidden');
        }
      }
    };

    form.querySelectorAll('input[type="email"],input[type="password"]').forEach(inp => {
      inp.addEventListener('blur', () => { touched[inp.name] = true; paint(inp.name); });
      inp.addEventListener('input', () => { if (touched[inp.name]) paint(inp.name); });
    });

    const eye = login.querySelector('[data-fv-login-eye]');
    eye?.addEventListener('click', () => {
      const pw = form.querySelector('[name="password"]');
      pw.type = pw.type === 'password' ? 'text' : 'password';
      eye.innerHTML = `<i data-lucide="${pw.type === 'password' ? 'eye' : 'eye-off'}" class="lucide-xs"></i>`;
      window.lucide && lucide.createIcons();
    });

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      touched.email = true; touched.password = true;
      paint('email'); paint('password');
      const emailVal = form.querySelector('[name="email"]').value;
      if (validate('email', emailVal) || validate('password', form.querySelector('[name="password"]').value)) return;
      const submit = login.querySelector('[data-fv-login-submit]');
      const btnText = login.querySelector('[data-fv-login-btn-text]');
      submit.disabled = true;
      btnText.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" style="animation:spin 1s linear infinite"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" opacity=".75"/></svg> Signing in...';
      setTimeout(() => {
        login.querySelector('[data-fv-login-form]').classList.add('hidden');
        const success = login.querySelector('[data-fv-login-success]');
        success.classList.remove('hidden');
        success.classList.add('flex');
        login.querySelector('[data-fv-login-success-email]').textContent = emailVal;
      }, 1500);
    });

    login.querySelector('[data-fv-login-reset]').addEventListener('click', () => {
      const success = login.querySelector('[data-fv-login-success]');
      success.classList.add('hidden');
      success.classList.remove('flex');
      login.querySelector('[data-fv-login-form]').classList.remove('hidden');
      form.reset();
      const submit = login.querySelector('[data-fv-login-submit]');
      submit.disabled = false;
      login.querySelector('[data-fv-login-btn-text]').textContent = 'Sign In';
      Object.keys(touched).forEach(k => delete touched[k]);
      form.querySelectorAll('input').forEach(i => i.classList.remove('fe-input-error','fe-input-success'));
      form.querySelectorAll('[data-fv-login-msg]').forEach(m => m.classList.add('hidden'));
      form.querySelectorAll('[data-fv-login-status]').forEach(m => m.classList.add('hidden'));
    });
  }

  // ─── 6. Credit Card Form ───────────────────────────────────
  const cf = document.querySelector('[data-fv-card-form]');
  if (cf) {
    const form = { number: '', name: '', expiry: '', cvv: '' };
    const numInput = cf.querySelector('[data-fv-card-num]');
    const nameInput = cf.querySelector('[data-fv-card-name]');
    const expInput = cf.querySelector('[data-fv-card-expiry]');
    const cvvInput = cf.querySelector('[data-fv-card-cvv]');
    const display = cf.querySelector('[data-fv-card-display]');
    const nameDisp = cf.querySelector('[data-fv-card-name-display]');
    const expDisp = cf.querySelector('[data-fv-card-expiry-display]');
    const typeBox = cf.querySelector('[data-fv-card-type]');
    const detected = cf.querySelector('[data-fv-card-detected]');
    const detectedType = cf.querySelector('[data-fv-card-detected-type]');

    const detect = (n) => /^4/.test(n) ? 'visa' : /^5[1-5]/.test(n) ? 'mastercard' : /^3[47]/.test(n) ? 'amex' : null;

    const updateType = () => {
      const t = detect(form.number.replace(/\s/g, ''));
      if (t === 'visa') typeBox.innerHTML = '<span class="text-white font-black text-2xl" style="font-family:serif;font-style:italic;letter-spacing:-.025em">VISA</span>';
      else if (t === 'mastercard') typeBox.innerHTML = '<div class="flex" style="margin-left:-8px"><div class="w-7 h-7 rounded-full bg-red-500" style="opacity:.9"></div><div class="w-7 h-7 rounded-full bg-amber-400" style="opacity:.9;margin-left:-8px"></div></div>';
      else if (t === 'amex') typeBox.innerHTML = '<span class="text-white font-bold text-xs tracking-widest uppercase">American Express</span>';
      else typeBox.innerHTML = '<div class="text-white opacity-40"><i data-lucide="credit-card" class="lucide-md"></i></div>';
      if (t) { detected.classList.remove('hidden'); detected.classList.add('flex'); detectedType.textContent = t; }
      else { detected.classList.add('hidden'); detected.classList.remove('flex'); }
      window.lucide && lucide.createIcons();
    };

    numInput.addEventListener('input', () => {
      const d = numInput.value.replace(/\D/g, '').slice(0, 16);
      form.number = d.replace(/(.{4})/g, '$1 ').trim();
      numInput.value = form.number;
      display.textContent = form.number || '•••• •••• •••• ••••';
      updateType();
    });
    nameInput.addEventListener('input', () => { form.name = nameInput.value; nameDisp.textContent = nameInput.value || 'YOUR NAME'; });
    expInput.addEventListener('input', () => {
      const d = expInput.value.replace(/\D/g, '').slice(0, 4);
      form.expiry = d.length <= 2 ? d : d.slice(0,2) + '/' + d.slice(2);
      expInput.value = form.expiry;
      expDisp.textContent = form.expiry || 'MM/YY';
    });
    cvvInput.addEventListener('input', () => {
      form.cvv = cvvInput.value.replace(/\D/g, '').slice(0, 4);
      cvvInput.value = form.cvv;
    });

    const cvvEye = cf.querySelector('[data-fv-card-cvv-eye]');
    cvvEye?.addEventListener('click', () => {
      cvvInput.type = cvvInput.type === 'password' ? 'text' : 'password';
      cvvEye.innerHTML = `<i data-lucide="${cvvInput.type === 'password' ? 'eye' : 'eye-off'}" class="lucide-xs"></i>`;
      window.lucide && lucide.createIcons();
    });

    const submit = cf.querySelector('[data-fv-card-submit]');
    submit.addEventListener('click', (e) => {
      e.preventDefault();
      const errs = {};
      const numDigits = form.number.replace(/\s/g, '').length;
      if (!form.number) errs.number = 'Card number is required';
      else if (numDigits < 16) errs.number = 'Enter a complete 16-digit card number';
      if (!form.name.trim()) errs.name = 'Cardholder name is required';
      else if (form.name.trim().length < 3) errs.name = 'Enter your full name as it appears on the card';
      const [mm, yy] = (form.expiry || '').split('/');
      if (!form.expiry) errs.expiry = 'Expiry date is required';
      else if (!mm || !yy || parseInt(mm) > 12 || parseInt(mm) < 1) errs.expiry = 'Enter a valid MM/YY date';
      if (!form.cvv) errs.cvv = 'CVV is required';
      else if (form.cvv.length < 3) errs.cvv = 'CVV must be 3-4 digits';

      ['number','name','expiry','cvv'].forEach(k => {
        const inp = cf.querySelector('[data-fv-card-' + (k === 'number' ? 'num' : k === 'name' ? 'name' : k === 'expiry' ? 'expiry' : 'cvv') + ']');
        const err = cf.querySelector('[data-fv-card-' + (k === 'number' ? 'num' : k === 'name' ? 'name' : k === 'expiry' ? 'expiry' : 'cvv') + '-err]');
        if (errs[k]) { inp.classList.add('fe-input-error'); err.textContent = errs[k]; err.classList.remove('hidden'); }
        else { inp.classList.remove('fe-input-error'); err.classList.add('hidden'); }
      });

      if (!Object.keys(errs).length) {
        cf.querySelector('[data-fv-card-pane]').classList.add('hidden');
        const ok = cf.querySelector('[data-fv-card-success]');
        ok.classList.remove('hidden');
        ok.classList.add('flex');
        cf.querySelector('[data-fv-card-success-last]').textContent = form.number.replace(/\s/g,'').slice(-4);
      }
    });

    cf.querySelector('[data-fv-card-reset]').addEventListener('click', () => {
      Object.keys(form).forEach(k => form[k] = '');
      [numInput,nameInput,expInput,cvvInput].forEach(i => i.value = '');
      display.textContent = '•••• •••• •••• ••••';
      nameDisp.textContent = 'YOUR NAME';
      expDisp.textContent = 'MM/YY';
      cf.querySelector('[data-fv-card-pane]').classList.remove('hidden');
      const ok = cf.querySelector('[data-fv-card-success]');
      ok.classList.add('hidden');
      ok.classList.remove('flex');
      cf.querySelectorAll('input').forEach(i => i.classList.remove('fe-input-error'));
      cf.querySelectorAll('[data-fv-card-num-err],[data-fv-card-name-err],[data-fv-card-expiry-err],[data-fv-card-cvv-err]').forEach(e => e.classList.add('hidden'));
      updateType();
    });
  }

  // ─── 7. Onboarding wizard ─────────────────────────────────
  const ow = document.querySelector('[data-fv-ow]');
  if (ow) {
    let step = 0;
    const data = { firstName:'',email:'',phone:'',company:'',role:'',companySize:'',industry:'' };
    const inputs = ow.querySelectorAll('[data-fv-ow-input]');
    inputs.forEach(inp => {
      inp.addEventListener('input', () => { data[inp.dataset.fvOwInput] = inp.value; updateNav(); });
      inp.addEventListener('change', () => { data[inp.dataset.fvOwInput] = inp.value; updateNav(); });
    });
    const labels = ['Personal Info','Company Info','Review'];
    const stepIcons = ['user','building','check-circle'];
    const stepColors = ['bg-primary-600','bg-violet-600','bg-emerald-600'];

    const canNext = () => {
      if (step === 0) return data.firstName.trim() && data.email.trim() && data.phone.trim();
      if (step === 1) return data.company.trim() && data.role.trim() && data.companySize && data.industry;
      return true;
    };

    const updateNav = () => {
      const next = ow.querySelector('[data-fv-ow-next]');
      if (step < 2) next.disabled = !canNext();
    };

    const render = () => {
      ow.querySelectorAll('[data-fv-ow-pane]').forEach(p => p.classList.toggle('hidden', +p.dataset.fvOwPane !== step));
      ow.querySelector('[data-fv-ow-counter]').textContent = (step + 1) + ' / 3';
      ow.querySelector('[data-fv-ow-progress]').style.width = (step === 0 ? 5 : step === 1 ? 50 : 100) + '%';
      ow.querySelectorAll('[data-fv-ow-step]').forEach(el => {
        const i = +el.dataset.fvOwStep;
        el.classList.remove('fv-ow-step-current','fv-ow-step-done','fv-ow-step-future','bg-primary-600','bg-violet-600','bg-emerald-600');
        if (i < step) el.classList.add('fv-ow-step-done');
        else if (i === step) el.classList.add('fv-ow-step-current', el.dataset.fvOwColor);
        else el.classList.add('fv-ow-step-future');
        el.innerHTML = `<i data-lucide="${i < step ? 'check-circle' : stepIcons[i]}" class="lucide-xs"></i>`;
      });
      ow.querySelectorAll('[data-fv-ow-step-label]').forEach(el => {
        const i = +el.dataset.fvOwStepLabel;
        el.classList.remove('fv-ow-step-label-current','fv-ow-step-label-done','fv-ow-step-label');
        if (i === step) el.classList.add('fv-ow-step-label-current');
        else if (i < step) el.classList.add('fv-ow-step-label-done');
        else el.classList.add('fv-ow-step-label');
      });
      ow.querySelectorAll('[data-fv-ow-line]').forEach(el => {
        const i = +el.dataset.fvOwLine;
        el.classList.toggle('fv-ow-line-done', i < step);
      });
      ow.querySelector('[data-fv-ow-back]').disabled = step === 0;
      const next = ow.querySelector('[data-fv-ow-next]');
      const finish = ow.querySelector('[data-fv-ow-finish]');
      if (step < 2) { next.classList.remove('hidden'); finish.classList.add('hidden'); next.disabled = !canNext(); }
      else { next.classList.add('hidden'); finish.classList.remove('hidden'); }
      if (step === 2) {
        const out = (k, v) => { const el = ow.querySelector('[data-fv-ow-out="' + k + '"]'); if (el) el.textContent = v || '—'; };
        Object.keys(data).forEach(k => out(k, data[k]));
      }
      window.lucide && lucide.createIcons();
    };

    ow.querySelector('[data-fv-ow-next]').addEventListener('click', () => { if (canNext()) { step++; render(); } });
    ow.querySelector('[data-fv-ow-back]').addEventListener('click', () => { if (step > 0) { step--; render(); } });
    ow.querySelector('[data-fv-ow-finish]').addEventListener('click', () => {
      [...ow.children].forEach(c => { if (!c.hasAttribute('data-fv-ow-success')) c.classList.add('hidden'); });
      const success = ow.querySelector('[data-fv-ow-success]');
      success.classList.remove('hidden');
      success.classList.add('flex');
      ow.querySelector('[data-fv-ow-name]').textContent = data.firstName.split(' ')[0];
      ow.querySelector('[data-fv-ow-company]').textContent = data.company;
      ow.querySelector('[data-fv-ow-email]').textContent = data.email;
    });
    ow.querySelector('[data-fv-ow-reset]').addEventListener('click', () => {
      step = 0;
      Object.keys(data).forEach(k => data[k] = '');
      inputs.forEach(i => i.value = '');
      [...ow.children].forEach(c => c.classList.remove('hidden'));
      const success = ow.querySelector('[data-fv-ow-success]');
      success.classList.add('hidden');
      success.classList.remove('flex');
      render();
    });
    render();
  }

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: general ===== */
if (window.LINER_PAGE === 'general.html') {
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors(); const B = LinerApexBase();

  new ApexCharts(document.querySelector('#chart-revenue-exp'), {
    ...B, chart: { ...B.chart, type:'bar', height:260 },
    series: [
      { name:'Revenue',  data:[32000,38000,35000,42000,48000,52000,46000,55000,61000] },
      { name:'Expenses', data:[22000,24000,21000,26000,28000,30000,27000,29000,31000] },
    ],
    colors: [C.primary, C.emerald],
    plotOptions: { bar:{ borderRadius:6, columnWidth:'50%' } },
    dataLabels: { enabled:false },
    xaxis: { ...B.xaxis, categories:['Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar'] },
    yaxis: { ...B.yaxis, labels:{ style:{ colors:C.axis }, formatter:v => '$'+(v/1000)+'k' } },
    legend: { show:false },
  }).render();

  new ApexCharts(document.querySelector('#chart-proj-dist'), {
    chart: { type:'donut', height:180, sparkline:{ enabled:true } },
    series: [40, 25, 20, 15],
    colors: [C.primary, C.cyan, C.emerald, C.amber],
    plotOptions: { pie:{ donut:{ size:'70%' } } },
    stroke: { width:0 }, dataLabels: { enabled:false },
  }).render();

  new ApexCharts(document.querySelector('#chart-productivity'), {
    ...B, chart: { ...B.chart, type:'area', height:240 },
    series: [
      { name:'Tasks',   data:[18,24,20,32,28,14,8] },
      { name:'Bugs',    data:[4,6,3,5,2,1,0] },
      { name:'Reviews', data:[6,8,12,9,14,5,2] },
    ],
    colors: [C.primary, C.rose, C.emerald],
    stroke: { curve:'smooth', width:2 },
    fill: { type:'gradient', gradient:{ opacityFrom:0.15, opacityTo:0 } },
    dataLabels: { enabled:false },
    xaxis: { ...B.xaxis, categories:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] },
    legend: { show:false },
  }).render();

  // Task Board tabs — filter rows by status
  const tasks = document.querySelectorAll('.gd-task');
  document.querySelectorAll('[data-task-tab]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-task-tab]').forEach(b => b.classList.remove('ec-tab-pill-active'));
      btn.classList.add('ec-tab-pill-active');
      const filter = btn.dataset.taskTab;
      tasks.forEach(t => t.style.display = (filter === 'all' || t.dataset.status === filter) ? '' : 'none');
    });
  });

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: invoice-list ===== */
if (window.LINER_PAGE === 'invoice-list.html') {
document.addEventListener('DOMContentLoaded', () => {
  // Filter pills (All / Paid / Pending / Overdue)
  document.querySelectorAll('[data-inv-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-inv-filter]').forEach(b => b.classList.remove('inv-filter-active'));
      btn.classList.add('inv-filter-active');
    });
  });
  window.lucide && lucide.createIcons();
});

}

/* ===== Page: line-chart (markup inline, charts via Apex) ===== */
if (window.LINER_PAGE === 'line-chart.html') {
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors(); const B = LinerApexBase();
  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

  // Monthly Sales
  new ApexCharts(document.querySelector('#lc-monthly'), {
    ...B, chart:{ ...B.chart, type:'line', height:280 },
    series:[{ name:'Sales', data:[4000,3400,5100,4700,6200,5800,7100,6600,7800,8200,9100,9800] }],
    colors:[C.primary],
    stroke:{ curve:'straight', width:2.5 },
    markers:{ size:3, strokeWidth:0, hover:{ size:6 } },
    dataLabels:{ enabled:false },
    xaxis:{ ...B.xaxis, categories: months },
    yaxis:{ ...B.yaxis, labels:{ ...B.yaxis.labels, formatter:(v)=>v/1000+'k' } },
  }).render();

  // Product Performance — 3 lines
  new ApexCharts(document.querySelector('#lc-products'), {
    ...B, chart:{ ...B.chart, type:'line', height:280 },
    series:[
      { name:'Product A', data:[2800,3100,2600,3400,3900,3600,4200,4800] },
      { name:'Product B', data:[1900,2200,2800,2500,3100,3400,2900,3500] },
      { name:'Product C', data:[3200,2800,3500,3100,2900,3700,4100,3800] },
    ],
    colors:[C.primary, C.cyan, C.emerald],
    stroke:{ curve:'straight', width:2 },
    markers:{ size:0, hover:{ size:5 } },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'] },
    yaxis:{ ...B.yaxis, labels:{ ...B.yaxis.labels, formatter:(v)=>v/1000+'k' } },
  }).render();

  // Smooth Actual vs Forecast
  new ApexCharts(document.querySelector('#lc-smooth'), {
    ...B, chart:{ ...B.chart, type:'line', height:280 },
    series:[
      { name:'Actual',   data:[3200,2900,4100,3800,5200,4600,5800,6200] },
      { name:'Forecast', data:[3000,3200,3500,3800,4200,4600,5100,5600] },
    ],
    colors:[C.primary, C.violet],
    stroke:{ curve:'smooth', width:[2.5, 2], dashArray:[0, 6] },
    markers:{ size:[4, 0], strokeColors:[C.primary], strokeWidth:[2, 0], fillColors:['#fff'] },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories:['W1','W2','W3','W4','W5','W6','W7','W8'] },
    yaxis:{ ...B.yaxis, labels:{ ...B.yaxis.labels, formatter:(v)=>v/1000+'k' } },
  }).render();

  // Step Line — Sales Funnel
  new ApexCharts(document.querySelector('#lc-step'), {
    ...B, chart:{ ...B.chart, type:'line', height:280 },
    series:[
      { name:'This Year', data:[8400,6200,4800,3200,2400,1800] },
      { name:'Last Year', data:[7200,5800,4100,3500,1900,1200] },
    ],
    colors:[C.emerald, C.rose],
    stroke:{ curve:'stepline', width:[2.5, 2], dashArray:[0, 5] },
    markers:{ size:[4, 3], strokeWidth:0 },
    dataLabels:{ enabled:false },
    legend:{ ...B.legend, position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px' },
    xaxis:{ ...B.xaxis, categories:['Lead','Qualified','Proposal','Negotiation','Closed Won','Renewal'], labels:{ ...B.xaxis.labels, style:{ fontSize:'10px', colors:C.axis } } },
    yaxis:{ ...B.yaxis, labels:{ ...B.yaxis.labels, formatter:(v)=>v/1000+'k' } },
  }).render();

  // Sparklines
  const spark = (id, color, data) => {
    new ApexCharts(document.querySelector('#' + id), {
      chart:{ type:'line', height:60, sparkline:{ enabled:true } },
      series:[{ data }],
      colors:[color],
      stroke:{ curve:'smooth', width:2 },
    }).render();
  };
  spark('lc-spark-views',  C.primary, [42,58,51,67,62,74,71]);
  spark('lc-spark-clicks', C.cyan,    [18,24,21,29,26,34,31]);
  spark('lc-spark-conv',   C.emerald, [6,9,7,12,10,14,13]);
  spark('lc-spark-perf',   C.amber,   [88,82,90,86,93,89,95]);

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: login ===== */
if (window.LINER_PAGE === 'login.html') {
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('login-form');
  if (form) form.addEventListener('submit', e => { e.preventDefault(); window.location.href = 'dashboard.html'; });
  const eye = document.getElementById('eye-btn');
  if (eye) eye.addEventListener('click', () => {
    const f = document.getElementById('pwd');
    const isPw = f.type === 'password';
    f.type = isPw ? 'text' : 'password';
    eye.querySelector('i').setAttribute('data-lucide', isPw ? 'eye-off' : 'eye');
    lucide.createIcons();
  });
  window.lucide && lucide.createIcons();
});

}

/* ===== Page: lucide-icons ===== */
if (window.LINER_PAGE === 'lucide-icons.html') {
document.addEventListener('DOMContentLoaded', () => {
  const icons = ['activity','airplay','alarm-clock','alert-circle','alert-triangle','align-center','align-justify','align-left','align-right','anchor','aperture','archive','arrow-down','arrow-up','arrow-left','arrow-right','at-sign','award','bar-chart','battery','bell','bluetooth','bold','book','bookmark','box','briefcase','calendar','camera','cast','check','check-circle','check-square','chevron-down','chevron-left','chevron-right','chevron-up','circle','clipboard','clock','cloud','code','codepen','codesandbox','coffee','columns','command','compass','copy','corner-down-left','cpu','credit-card','crop','crosshair','database','delete','disc','dollar-sign','download','droplet','edit','edit-2','edit-3','external-link','eye','eye-off','facebook','feather','figma','file','file-text','film','filter','flag','folder','frown','gift','git-branch','git-commit','git-merge','git-pull-request','github','gitlab','globe','grid','hard-drive','hash','headphones','heart','help-circle','hexagon','home','image','inbox','info','instagram','italic','key','layers','layout','life-buoy','link','linkedin','list','loader','lock','log-in','log-out','mail','map','map-pin','maximize','meh','menu','message-circle','message-square','mic','mic-off','minimize','minus','minus-circle','minus-square','monitor','moon','more-horizontal','more-vertical','mouse-pointer','move','music','navigation','octagon','package','paperclip','pause','pause-circle','pen-tool','percent','phone','pie-chart','play','plus','pocket','power','printer','radio','refresh-cw','repeat','rewind','rotate-cw','rss','save','scissors','search','send','server','settings','share','shield','shopping-bag','shopping-cart','shuffle','sidebar','skip-back','skip-forward','slack','slash','sliders','smartphone','smile','speaker','square','star','sun','sunrise','sunset','tablet','tag','target','terminal','thermometer','thumbs-down','thumbs-up','toggle-left','toggle-right','tool','trash','trash-2','trello','trending-down','trending-up','triangle','truck','tv','twitch','twitter','type','umbrella','underline','unlock','upload','user','user-check','user-plus','user-x','users','video','voicemail','volume','watch','wifi','wind','x','x-circle','x-octagon','x-square','youtube','zap','zoom-in','zoom-out'];
  function render(filter = '') {
    const list = filter ? icons.filter(n => n.includes(filter)) : icons;
    document.getElementById('grid').innerHTML = list.map(n => `
      <div class="col-6 col-md-3 col-lg-2"><div class="icon-tile">
        <i data-lucide="${n}" class="lucide-lg"></i>
        <div class="icon-tile-label">${n}</div>
      </div></div>`).join('');
    lucide.createIcons();
  }
  render();
  document.getElementById('q').addEventListener('input', e => render(e.target.value.toLowerCase()));
});

}

/* ===== Page: music ===== */
if (window.LINER_PAGE === 'music.html') {
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors(); const B = LinerApexBase();

  new ApexCharts(document.querySelector('#chart-streams'), {
    ...B, chart: { ...B.chart, type:'area', height:200 },
    series: [
      { name:'Plays',   data:[42,68,55,80,95,120,88] },
      { name:'Minutes', data:[168,272,220,320,380,480,352] },
    ],
    colors: [C.cyan, C.violet],
    stroke: { curve:'smooth', width:[2.5,2], dashArray:[0,4] },
    fill: { type:'gradient', gradient:{ opacityFrom:0.2, opacityTo:0 } },
    dataLabels: { enabled:false },
    xaxis: { ...B.xaxis, categories:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] },
    legend: { show:false },
  }).render();

  new ApexCharts(document.querySelector('#spark-listeners'), {
    chart: { type:'area', height:60, sparkline:{ enabled:true } },
    series: [{ data:[20,28,22,35,30,42,38] }],
    colors: [C.cyan], stroke: { curve:'smooth', width:2 },
    fill: { type:'gradient', gradient:{ opacityFrom:0.3, opacityTo:0 } },
  }).render();
  new ApexCharts(document.querySelector('#spark-streams'), {
    chart: { type:'bar', height:60, stacked:true, sparkline:{ enabled:true } },
    series: [{ name:'a', data:[30,45,35,55,40,60,50] }, { name:'b', data:[15,20,25,18,22,15,20] }],
    colors: [C.primary, C.violet],
    plotOptions: { bar:{ borderRadius:3, columnWidth:'30%' } },
  }).render();
  new ApexCharts(document.querySelector('#spark-albums'), {
    chart: { type:'radialBar', height:100, sparkline:{ enabled:true } },
    series: [75,55],
    colors: [C.cyan, C.violet],
    plotOptions: { radialBar:{ hollow:{ size:'50%' }, track:{ background:C.grid } } },
  }).render();

  new ApexCharts(document.querySelector('#chart-weekly'), {
    ...B, chart: { ...B.chart, type:'bar', height:180 },
    series: [
      { name:'Plays',   data:[42,68,55,80,95,120,88] },
      { name:'Minutes', data:[168,272,220,320,380,480,352] },
    ],
    colors: [C.primary, C.cyan],
    plotOptions: { bar:{ borderRadius:4, columnWidth:'40%' } },
    dataLabels: { enabled:false },
    xaxis: { ...B.xaxis, categories:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] },
    legend: { show:false },
  }).render();

  new ApexCharts(document.querySelector('#chart-genre'), {
    chart: { type:'radialBar', height:160 },
    series: [35, 65, 48, 78, 25],
    labels: ['Pop','Rock','Jazz','Electronic','Classical'],
    colors: [C.primary, C.violet, C.cyan, C.emerald, C.amber],
    plotOptions: { radialBar:{ hollow:{ size:'30%' }, track:{ background:C.grid }, dataLabels:{ show:false } } },
    stroke: { lineCap:'round' },
    legend: { show:false },
  }).render();

  // Player interactivity
  let isPlaying = true, liked = false, shuffle = false;
  const playBtn = document.getElementById('play-btn');
  const likeBtn = document.getElementById('like-btn');
  const shuffleBtn = document.getElementById('shuffle-btn');
  playBtn?.addEventListener('click', () => {
    isPlaying = !isPlaying;
    playBtn.innerHTML = `<i data-lucide="${isPlaying ? 'pause' : 'play'}" class="lucide-md"></i>`;
    document.querySelector('.disc-spin')?.classList.toggle('disc-paused', !isPlaying);
    window.lucide && lucide.createIcons();
  });
  likeBtn?.addEventListener('click', () => { liked = !liked; likeBtn.classList.toggle('liked', liked); });
  shuffleBtn?.addEventListener('click', () => { shuffle = !shuffle; shuffleBtn.classList.toggle('active', shuffle); });

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: popover ===== */
if (window.LINER_PAGE === 'popover.html') {
document.addEventListener('DOMContentLoaded', () => {
  const popovers = [...document.querySelectorAll('[data-po]')];
  popovers.forEach(wrap => {
    const trigger = wrap.querySelector('[data-po-trigger]');
    const panel = wrap.querySelector('[data-po-content]');
    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      const wasOpen = wrap.classList.contains('po-open');
      popovers.forEach(p => p.classList.remove('po-open'));
      if (!wasOpen) wrap.classList.add('po-open');
    });
    panel.addEventListener('click', (e) => e.stopPropagation());
  });
  document.addEventListener('click', () => popovers.forEach(p => p.classList.remove('po-open')));

  // Toggle switches
  document.querySelectorAll('[data-po-toggle]').forEach(t => {
    t.addEventListener('click', () => t.classList.toggle('po-toggle-on'));
  });

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: radialbar-chart ===== */
if (window.LINER_PAGE === 'radialbar-chart.html') {
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors(); const B = LinerApexBase();

  // Team Performance (multi radialBar)
  const teamData = [
    { name:'Alice', performance:92, fill:C.primary },
    { name:'Bob',   performance:78, fill:C.cyan },
    { name:'Carol', performance:85, fill:C.emerald },
    { name:'David', performance:63, fill:C.amber },
    { name:'Eve',   performance:97, fill:C.violet },
  ];
  new ApexCharts(document.querySelector('#rc-team'), {
    chart:{ type:'radialBar', height:300, fontFamily:'Inter, sans-serif', toolbar:{ show:false } },
    series: teamData.map(d => d.performance),
    labels: teamData.map(d => d.name),
    colors: teamData.map(d => d.fill),
    plotOptions:{ radialBar:{ hollow:{ size:'25%' }, track:{ background:C.grid, margin:6 }, dataLabels:{ name:{ show:false }, value:{ show:false }, total:{ show:false } }, barLabels:{ enabled:false } } },
    stroke:{ lineCap:'round' },
    legend:{ show:false },
  }).render();
  const tlegend = document.getElementById('rc-team-legend');
  if (tlegend) {
    tlegend.innerHTML = teamData.map(d => `<div class="flex items-center gap-1-5"><span class="w-2-5 h-2-5 rounded-full shrink-0" style="background-color:${d.fill}"></span><span class="text-xs text-slate-600">${d.name}</span><span class="text-xs font-bold text-slate-700">${d.performance}%</span></div>`).join('');
  }

  // Multi-ring Progress
  const ringData = [
    { name:'Revenue', value:87, fill:C.primary },
    { name:'Customers', value:72, fill:C.cyan },
    { name:'Conversion', value:64, fill:C.emerald },
    { name:'Retention', value:93, fill:C.rose },
  ];
  new ApexCharts(document.querySelector('#rc-multi'), {
    chart:{ type:'radialBar', height:300, fontFamily:'Inter, sans-serif', toolbar:{ show:false } },
    series: ringData.map(d => d.value),
    labels: ringData.map(d => d.name),
    colors: ringData.map(d => d.fill),
    plotOptions:{ radialBar:{ hollow:{ size:'30%' }, track:{ background:C.grid, margin:6 }, dataLabels:{ name:{ show:false }, value:{ show:false }, total:{ show:false } } } },
    stroke:{ lineCap:'round' },
    legend:{ show:false },
  }).render();
  const mlegend = document.getElementById('rc-multi-legend');
  if (mlegend) {
    mlegend.innerHTML = ringData.map(d => `<div class="flex items-center gap-1-5"><span class="w-2-5 h-2-5 rounded-full shrink-0" style="background-color:${d.fill}"></span><span class="text-xs text-slate-600">${d.name}</span><span class="text-xs font-bold text-slate-700">${d.value}%</span></div>`).join('');
  }

  // Skills Radar (Team A vs Team B)
  new ApexCharts(document.querySelector('#rc-radar'), {
    chart:{ type:'radar', height:300, fontFamily:'Inter, sans-serif', toolbar:{ show:false }, foreColor:C.text },
    series:[
      { name:'Team A', data:[88,92,74,80,96,85] },
      { name:'Team B', data:[72,85,90,68,78,94] },
    ],
    colors:[C.primary, C.cyan],
    stroke:{ width:2 },
    fill:{ opacity:0.2 },
    markers:{ size:0 },
    legend:{ position:'bottom', markers:{ width:8, height:8, radius:9 }, fontSize:'11px', labels:{ colors:C.text } },
    xaxis:{ categories:['Communication','Technical','Leadership','Creativity','Teamwork','Delivery'], labels:{ style:{ fontSize:'11px', colors:C.axis } } },
    yaxis:{ show:false },
    plotOptions:{ radar:{ polygons:{ strokeColors:C.grid, fill:{ colors:['transparent'] } } } },
  }).render();

  // Progress Radial Cards (4 individual)
  const progress = [
    { id:'rc-prog-1', value:84, color:C.primary },
    { id:'rc-prog-2', value:67, color:C.cyan },
    { id:'rc-prog-3', value:91, color:C.emerald },
    { id:'rc-prog-4', value:58, color:C.amber },
  ];
  progress.forEach(p => {
    new ApexCharts(document.querySelector('#' + p.id), {
      chart:{ type:'radialBar', height:130, width:130, fontFamily:'Inter, sans-serif', toolbar:{ show:false }, sparkline:{ enabled:true } },
      series:[p.value],
      colors:[p.color],
      plotOptions:{ radialBar:{ hollow:{ size:'62%' }, track:{ background:C.grid }, dataLabels:{ name:{ show:false }, value:{ show:false } } } },
      stroke:{ lineCap:'round' },
    }).render();
  });

  // Fix radar fill opacity (React used 0.2)
  const styleId = '__rc-radar-fix';
  if (!document.getElementById(styleId)) {
    const s = document.createElement('style');
    s.id = styleId;
    s.textContent = '.apexcharts-radar-series .apexcharts-series path{fill-opacity:.18!important}';
    document.head.appendChild(s);
  }

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: rating ===== */
if (window.LINER_PAGE === 'rating.html') {
document.addEventListener('DOMContentLoaded', () => {
  // Interactive star ratings
  document.querySelectorAll('[data-rt]').forEach(group => {
    const id = group.dataset.rt;
    const btns = [...group.querySelectorAll('.rt-btn')];
    const display = document.querySelector(`[data-rt-display="${id}"]`);
    const srLabel = document.querySelector('[data-sr-label]');
    const srRatingLabels = ['', 'Very Poor', 'Poor', 'Average', 'Good', 'Excellent'];
    const paint = (v) => {
      btns.forEach((b, i) => {
        const svg = b.querySelector('svg');
        svg.classList.toggle('rt-star-on', i < v);
        svg.classList.toggle('rt-star-off', i >= v);
      });
      group.dataset.rtVal = v;
      if (display) display.textContent = v;
      if (id === 'sr' && srLabel) srLabel.textContent = srRatingLabels[v];
    };
    btns.forEach((b, i) => {
      b.addEventListener('mouseenter', () => paint(i + 1));
      b.addEventListener('mouseleave', () => paint(+group.dataset.rtVal));
      b.addEventListener('click', () => paint(i + 1));
    });
  });

  // Submit Rating
  const sub = document.querySelector('[data-sr-submit]');
  if (sub) {
    sub.addEventListener('click', () => {
      const sr = document.querySelector('[data-rt="sr"]');
      const v = +sr.dataset.rtVal || 0;
      if (!v) return;
      document.querySelector('[data-sr-form]').classList.add('hidden');
      const success = document.querySelector('[data-sr-success]');
      success.classList.remove('hidden');
      document.querySelector('[data-sr-val]').textContent = v;
    });
    document.querySelector('[data-sr-reset]').addEventListener('click', () => {
      document.querySelector('[data-sr-form]').classList.remove('hidden');
      document.querySelector('[data-sr-success]').classList.add('hidden');
    });
  }

  // Emoji ratings
  const emojiLabels = ['', 'Terrible', 'Poor', 'Okay', 'Good', 'Amazing'];
  document.querySelectorAll('[data-em]').forEach(btn => {
    btn.addEventListener('click', () => {
      const v = +btn.dataset.em;
      document.querySelectorAll('[data-em]').forEach(b => {
        b.classList.remove('em-rate-active');
        b.className = b.className.replace(/(bg-(red|orange|amber|lime|emerald)-100|ring-2|ring-(red|orange|amber|lime|emerald)-500)/g, '').replace(/\s+/g, ' ').trim();
        if (!b.classList.contains('em-rate')) b.classList.add('em-rate');
      });
      btn.classList.add('em-rate-active', btn.dataset.emBg, 'ring-2', btn.dataset.emRing);
      btn.querySelector('[data-em-label]').classList.remove('text-slate-500');
      btn.querySelector('[data-em-label]').classList.add('text-slate-800');
      const result = document.querySelector('[data-em-result]');
      result.classList.remove('hidden');
      document.querySelector('[data-em-selected]').textContent = emojiLabels[v];
    });
  });

  // Color-coded ratings
  const barColors = ['bg-red-500', 'bg-orange-500', 'bg-amber-500', 'bg-lime-500', 'bg-emerald-500'];
  const textColors = ['text-red-600', 'text-orange-600', 'text-amber-600', 'text-lime-600', 'text-emerald-600'];
  const crLabels = ['Poor', 'Fair', 'Good', 'Great', 'Excellent'];
  const crState = {};
  document.querySelectorAll('[data-cr]').forEach(row => { crState[row.dataset.cr] = +row.dataset.crVal; });

  const updateAvg = () => {
    const vals = Object.values(crState);
    const avgRaw = (vals.reduce((a, b) => a + b, 0) / vals.length).toFixed(1);
    const avg = Math.round(vals.reduce((a, b) => a + b, 0) / vals.length);
    const bars = document.querySelector('[data-cr-avg-bars]');
    bars.innerHTML = [1,2,3,4,5].map(l => `<div class="w-5 h-2 rounded-full ${l <= avg ? barColors[avg - 1] : 'bg-slate-200'}"></div>`).join('');
    document.querySelector('[data-cr-avg-num]').textContent = avgRaw;
  };

  document.querySelectorAll('[data-cr]').forEach(row => {
    const bars = [...row.querySelectorAll('[data-cr-level]')];
    const labelEl = row.querySelector('[data-cr-label]');
    const paint = (v) => {
      bars.forEach((b, i) => {
        b.className = `cr-bar ${(i + 1) <= v ? barColors[v - 1] + ' cr-bar-active' : 'bg-slate-200'}`;
      });
      labelEl.className = `text-xs font-semibold ${textColors[v - 1]}`;
      labelEl.textContent = crLabels[v - 1];
      row.dataset.crVal = v;
      crState[row.dataset.cr] = v;
      updateAvg();
    };
    bars.forEach((b, i) => {
      b.addEventListener('click', () => paint(i + 1));
    });
  });

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: register ===== */
if (window.LINER_PAGE === 'register.html') {
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('reg-form');
  if (form) form.addEventListener('submit', e => { e.preventDefault(); window.location.href = 'dashboard.html'; });

  // Eye toggle
  const eyeBtn = document.getElementById('reg-eye-btn');
  const pwd = document.getElementById('reg-pwd');
  if (eyeBtn && pwd) {
    eyeBtn.addEventListener('click', () => {
      pwd.type = pwd.type === 'password' ? 'text' : 'password';
      const icon = eyeBtn.querySelector('i');
      icon.setAttribute('data-lucide', pwd.type === 'password' ? 'eye' : 'eye-off');
      window.lucide && lucide.createIcons();
    });
  }

  // Password strength
  const bars = document.querySelectorAll('#reg-strength-bars .strength-bar');
  const label = document.getElementById('reg-strength-label');
  const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
  const classes = ['', 'weak', 'fair', 'good', 'strong'];
  if (pwd && bars.length) {
    pwd.addEventListener('input', () => {
      let s = 0;
      const v = pwd.value;
      if (!v) {
        bars.forEach(b => b.className = 'flex-grow-1 strength-bar');
        label.textContent = 'Use 8+ chars with mixed case, numbers, and symbols';
        return;
      }
      if (v.length >= 8) s++;
      if (v.length >= 12) s++;
      if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
      if (/\d/.test(v)) s++;
      if (/[^A-Za-z0-9]/.test(v)) s++;
      s = Math.min(s, 4);
      bars.forEach((b, i) => {
        b.className = 'flex-grow-1 strength-bar';
        if (i < s) b.classList.add(classes[s]);
      });
      label.textContent = labels[s] + ' — Use 8+ chars with mixed case, numbers, and symbols';
    });
  }

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: tabs ===== */
if (window.LINER_PAGE === 'tabs.html') {
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-tb]').forEach(group => {
    const tabs = [...group.querySelectorAll('[data-tb-idx]')];
    const variant = group.dataset.tbVariant;
    // Pane container — sibling .tb-line-content, .tb-vert-content, .tb-folder-content, or next .p-4 etc.
    let paneRoot = group.querySelector('.tb-line-content, .tb-vert-content, .tb-folder-content');
    if (!paneRoot) paneRoot = group.parentElement.querySelector('.tb-line-content, .tb-vert-content, .tb-folder-content, [data-tb-scroll-content], .p-4');
    if (!paneRoot && variant === 'bordered') paneRoot = group.parentElement.querySelector('.p-4');
    tabs.forEach(t => t.addEventListener('click', () => {
      const idx = +t.dataset.tbIdx;
      tabs.forEach(x => x.classList.remove('tb-active'));
      t.classList.add('tb-active');
      // Status tabs: swap badge colors
      if (variant === 'status') {
        tabs.forEach(x => {
          const badge = x.querySelector('[data-tb-count]');
          if (badge) badge.className = 'tb-status-badge ' + (x === t ? x.dataset.tbBgActive : x.dataset.tbBg);
        });
      }
      if (paneRoot) {
        const panes = paneRoot.querySelectorAll('[data-tb-pane]');
        panes.forEach(p => p.classList.toggle('tb-pane-active', +p.dataset.tbPane === idx));
      }
    }));
  });

  // FAQ accordion (in product page)
  document.querySelectorAll('[data-tb-faq]').forEach(faq => {
    const btn = faq.querySelector('.tb-faq-btn');
    btn.addEventListener('click', () => faq.classList.toggle('tb-faq-open'));
  });

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: two-steps ===== */
if (window.LINER_PAGE === 'two-steps.html') {
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('otp-form');
  const btnText = document.getElementById('otp-btn-text');
  const submit = document.getElementById('otp-submit');
  const err = document.getElementById('otp-error');
  const resendBtn = document.getElementById('otp-resend');
  const resentMsg = document.getElementById('otp-resent');
  const arr = [...document.querySelectorAll('.otp-input')];

  arr.forEach((el, i) => {
    el.addEventListener('input', () => {
      el.value = el.value.replace(/\D/g, '').slice(0, 1);
      if (el.value && arr[i + 1]) arr[i + 1].focus();
      err.classList.add('d-none');
    });
    el.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !el.value && arr[i - 1]) arr[i - 1].focus();
    });
    el.addEventListener('paste', e => {
      const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
      if (!text) return;
      e.preventDefault();
      text.split('').forEach((c, j) => { if (arr[j]) arr[j].value = c; });
      if (arr[text.length - 1]) arr[Math.min(text.length, 5)].focus();
    });
  });

  if (form) form.addEventListener('submit', e => {
    e.preventDefault();
    const code = arr.map(a => a.value).join('');
    if (code.length < 6) {
      err.textContent = 'Please enter the complete 6-digit code';
      err.classList.remove('d-none');
      return;
    }
    btnText.textContent = 'Verifying...';
    setTimeout(() => { window.location.href = 'dashboard.html'; }, 800);
  });

  if (resendBtn) resendBtn.addEventListener('click', () => {
    arr.forEach(a => a.value = '');
    arr[0]?.focus();
    resendBtn.classList.add('d-none');
    resentMsg.classList.remove('d-none');
    setTimeout(() => {
      resentMsg.classList.add('d-none');
      resendBtn.classList.remove('d-none');
    }, 3000);
  });

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: maintenance ===== */
if (window.LINER_PAGE === 'maintenance.html') {
document.addEventListener('DOMContentLoaded', () => {
  const h = document.querySelector('[data-cd="h"]');
  const m = document.querySelector('[data-cd="m"]');
  const s = document.querySelector('[data-cd="s"]');
  const pad = n => String(n).padStart(2, '0');
  let time = { h: 4, m: 32, s: 47 };
  const tick = () => {
    if (time.s > 0) time.s--;
    else if (time.m > 0) { time.m--; time.s = 59; }
    else if (time.h > 0) { time.h--; time.m = 59; time.s = 59; }
    else return;
    if (h) h.textContent = pad(time.h);
    if (m) m.textContent = pad(time.m);
    if (s) s.textContent = pad(time.s);
  };
  setInterval(tick, 1000);

  const btn = document.getElementById('notify-btn');
  const email = document.getElementById('notify-email');
  const view = document.getElementById('notify-view');
  const done = document.getElementById('notify-done');
  const shown = document.getElementById('notify-shown');
  if (btn) btn.addEventListener('click', () => {
    if (!email.value) return;
    shown.textContent = email.value;
    view.classList.add('d-none');
    done.classList.remove('d-none');
  });

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: widgets ===== */
if (window.LINER_PAGE === 'widgets.html') {
document.addEventListener('DOMContentLoaded', () => {
  const C = LinerChartColors();
  const primaryHex = C.primary;

  // 1. Gradient stat sparklines (white area on colored bg)
  const sparkData = [
    [10,25,18,35,28,42,38],
    [45,30,55,40,65,50,70],
    [80,60,75,55,70,85,90],
    [20,35,25,45,38,55,48],
  ];
  sparkData.forEach((data, i) => {
    const el = document.querySelector('#wg-grad-spark-' + i);
    if (!el) return;
    new ApexCharts(el, {
      chart:{ type:'area', height:48, sparkline:{ enabled:true } },
      series:[{ data }],
      stroke:{ curve:'smooth', width:2 },
      colors:['#ffffff'],
      fill:{ type:'gradient', gradient:{ opacityFrom:.35, opacityTo:0 } },
      tooltip:{ enabled:false },
    }).render();
  });

  // 2. Light stat sparklines (primary area on light bg)
  sparkData.forEach((data, i) => {
    const el = document.querySelector('#wg-light-spark-' + i);
    if (!el) return;
    new ApexCharts(el, {
      chart:{ type:'area', height:40, sparkline:{ enabled:true } },
      series:[{ data }],
      stroke:{ curve:'smooth', width:1.5 },
      colors:[primaryHex],
      fill:{ type:'gradient', gradient:{ opacityFrom:.2, opacityTo:0 } },
      tooltip:{ enabled:false },
    }).render();
  });

  // 3. Social bar charts (last bar = primary, rest = light primary)
  const socialBars = [
    [30,45,38,55,48,60,52],
    [50,65,58,72,66,80,74],
    [20,35,28,42,38,50,45],
    [15,28,22,35,30,42,38],
  ];
  const styleP200 = getComputedStyle(document.documentElement).getPropertyValue('--p-200').trim();
  const p200rgb = styleP200 ? `rgb(${styleP200.split(/\s+/).join(',')})` : '#bfdbfe';
  socialBars.forEach((data, i) => {
    const el = document.querySelector('#wg-social-bars-' + i);
    if (!el) return;
    new ApexCharts(el, {
      chart:{ type:'bar', height:48, sparkline:{ enabled:true } },
      series:[{ data }],
      plotOptions:{ bar:{ columnWidth:'55%', distributed:true, borderRadius:3, borderRadiusApplication:'end' } },
      colors: data.map((_, idx) => idx === data.length - 1 ? primaryHex : p200rgb),
      legend:{ show:false },
      xaxis:{ categories: data.map((_, idx) => idx), labels:{ show:false }, axisBorder:{ show:false }, axisTicks:{ show:false } },
      yaxis:{ show:false },
      tooltip:{ enabled:false },
    }).render();
  });

  // 4. Todo Widget interactions
  const todoRoot = document.querySelector('[data-wg-todo]');
  if (todoRoot) {
    const list = todoRoot.querySelector('[data-wg-todo-list]');
    const countEl = todoRoot.querySelector('[data-wg-todo-count]');
    const input = todoRoot.querySelector('[data-wg-todo-input]');
    const addBtn = todoRoot.querySelector('[data-wg-todo-add]');

    const updateCount = () => {
      const open = list.querySelectorAll('[data-wg-todo-id]:not(.wg-todo-done)').length;
      countEl.textContent = open + ' left';
    };

    const bindRow = (row) => {
      const toggle = row.querySelector('[data-wg-todo-toggle]');
      const removeBtn = row.querySelector('[data-wg-todo-remove]');
      const textEl = row.querySelector('.wg-todo-text');
      toggle.addEventListener('click', () => {
        const isDone = toggle.classList.toggle('wg-todo-cb-on');
        textEl.classList.toggle('wg-todo-text-done', isDone);
        row.classList.toggle('wg-todo-done', isDone);
        toggle.innerHTML = isDone ? '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' : '';
        updateCount();
      });
      removeBtn.addEventListener('click', () => { row.remove(); updateCount(); });
    };

    list.querySelectorAll('[data-wg-todo-id]').forEach(bindRow);
    // mark initially-checked rows
    list.querySelectorAll('.wg-todo-cb-on').forEach(cb => cb.closest('[data-wg-todo-id]')?.classList.add('wg-todo-done'));
    updateCount();

    let seq = list.querySelectorAll('[data-wg-todo-id]').length;
    const addTodo = () => {
      const text = input.value.trim();
      if (!text) return;
      seq++;
      const row = document.createElement('div');
      row.className = 'flex items-center gap-3 px-2 py-2-5 rounded-xl wg-todo-row';
      row.dataset.wgTodoId = String(seq);
      row.innerHTML = '<button class="wg-todo-cb" data-wg-todo-toggle></button><span class="flex-1 text-sm wg-todo-text">' + text.replace(/[<>&]/g, c => ({ '<':'&lt;', '>':'&gt;', '&':'&amp;' }[c])) + '</span><button class="wg-todo-rm" data-wg-todo-remove><i data-lucide="x" class="lucide-xs"></i></button>';
      list.appendChild(row);
      bindRow(row);
      input.value = '';
      updateCount();
      window.lucide && lucide.createIcons();
    };
    addBtn.addEventListener('click', addTodo);
    input.addEventListener('keydown', e => { if (e.key === 'Enter') addTodo(); });
  }

  // 5. Countdown widget — June 1, 2026
  const cdRoot = document.querySelector('[data-wg-countdown]');
  if (cdRoot) {
    const target = new Date('2026-06-01T00:00:00').getTime();
    const tick = () => {
      const diff = target - Date.now();
      const safe = Math.max(diff, 0);
      const days    = Math.floor(safe / (1000*60*60*24));
      const hours   = Math.floor((safe / (1000*60*60)) % 24);
      const minutes = Math.floor((safe / (1000*60)) % 60);
      const seconds = Math.floor((safe / 1000) % 60);
      const set = (k, v) => { const el = cdRoot.querySelector('[data-wg-cd="' + k + '"]'); if (el) el.textContent = String(v).padStart(2, '0'); };
      set('days', days); set('hours', hours); set('minutes', minutes); set('seconds', seconds);
    };
    tick();
    setInterval(tick, 1000);
  }

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: faq ===== */
if (window.LINER_PAGE === 'faq.html') {
document.addEventListener('DOMContentLoaded', () => {
  let activeCat = 'All';
  let query = '';
  const search = document.getElementById('faq-search');
  const clearBtn = document.getElementById('faq-search-clear');
  const items = Array.from(document.querySelectorAll('.faq-item'));
  const empty = document.getElementById('faq-empty');
  const pills = Array.from(document.querySelectorAll('.faq-pill'));

  function setCat(c) {
    activeCat = c;
    pills.forEach(p => p.classList.toggle('faq-pill-active', p.dataset.cat === c));
    apply();
  }
  function apply() {
    let shown = 0;
    items.forEach(el => {
      const matchCat = activeCat === 'All' || el.dataset.cat === activeCat;
      const matchQ = !query || el.dataset.q.toLowerCase().includes(query) || el.dataset.a.toLowerCase().includes(query);
      const show = matchCat && matchQ;
      el.style.display = show ? '' : 'none';
      if (show) shown++;
    });
    empty.classList.toggle('hidden', shown > 0);
  }

  items.forEach(el => {
    el.querySelector('.faq-item-btn').addEventListener('click', () => el.classList.toggle('faq-open'));
  });
  pills.forEach(p => p.addEventListener('click', () => setCat(p.dataset.cat)));
  document.querySelectorAll('.faq-cat, .faq-quick').forEach(b => b.addEventListener('click', () => { setCat(b.dataset.cat); if (search){ search.value=''; query=''; clearBtn.classList.add('hidden'); } apply(); }));
  search?.addEventListener('input', e => {
    query = e.target.value.toLowerCase();
    clearBtn.classList.toggle('hidden', !e.target.value);
    apply();
  });
  clearBtn?.addEventListener('click', () => { search.value=''; query=''; clearBtn.classList.add('hidden'); apply(); });
  document.getElementById('faq-empty-reset')?.addEventListener('click', () => { setCat('All'); if (search){ search.value=''; query=''; clearBtn.classList.add('hidden'); } apply(); });

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: pricing ===== */
if (window.LINER_PAGE === 'pricing.html') {
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('pr-toggle');
  const lblM = document.getElementById('pr-lbl-m');
  const lblY = document.getElementById('pr-lbl-y');
  let yearly = false;
  function apply() {
    toggle.classList.toggle('pr-toggle-on', yearly);
    lblM.className = 'text-sm font-semibold ' + (yearly ? 'text-slate-400' : 'text-slate-900');
    lblY.className = 'text-sm font-semibold flex items-center gap-2 ' + (yearly ? 'text-slate-900' : 'text-slate-400');
    document.querySelectorAll('[data-monthly]').forEach(el => {
      el.textContent = yearly ? el.dataset.yearly : el.dataset.monthly;
    });
    document.querySelectorAll('#pr-suffix-pro, #pr-suffix-ent').forEach(el => {
      el.textContent = yearly ? 'mo, billed yearly' : 'mo';
    });
    document.querySelectorAll('[data-yearly-note]').forEach(el => el.classList.toggle('hidden', !yearly));
  }
  toggle?.addEventListener('click', () => { yearly = !yearly; apply(); });

  // FAQ accordion
  document.querySelectorAll('.faq-item').forEach(el => {
    el.querySelector('.faq-item-btn').addEventListener('click', () => el.classList.toggle('faq-open'));
  });

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: apikeys ===== */
if (window.LINER_PAGE === 'apikeys.html') {
document.addEventListener('DOMContentLoaded', () => {
  // Show/hide key text
  document.querySelectorAll('.ak-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const span = btn.parentElement.querySelector('.ak-key-text');
      const showing = span.dataset.showing === '1';
      span.textContent = showing ? span.dataset.masked : span.dataset.full;
      span.dataset.showing = showing ? '0' : '1';
      const icon = btn.querySelector('[data-lucide]');
      icon.setAttribute('data-lucide', showing ? 'eye' : 'eye-off');
      window.lucide && lucide.createIcons();
    });
  });
  // Copy key
  document.querySelectorAll('.ak-copy').forEach(btn => {
    btn.addEventListener('click', () => {
      const span = btn.parentElement.querySelector('.ak-key-text');
      navigator.clipboard?.writeText(span.dataset.full);
      const icon = btn.querySelector('[data-lucide]');
      icon.setAttribute('data-lucide', 'check-circle');
      btn.classList.add('text-emerald-500');
      window.lucide && lucide.createIcons();
      setTimeout(() => {
        const ic = btn.querySelector('[data-lucide]');
        if (ic) { ic.setAttribute('data-lucide', 'copy'); btn.classList.remove('text-emerald-500'); window.lucide && lucide.createIcons(); }
      }, 2000);
    });
  });
  // Revoke / delete row
  document.querySelectorAll('.ak-revoke').forEach(btn => {
    btn.addEventListener('click', () => {
      const row = btn.closest('tr');
      row.dataset.keyStatus = 'Revoked';
      const statusCell = row.querySelector('td:nth-child(6) .badge');
      statusCell.className = 'badge badge-danger';
      statusCell.textContent = 'Revoked';
      btn.remove();
    });
  });
  document.querySelectorAll('.ak-delete').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('tr')?.remove());
  });
  // Webhook toggle
  document.querySelectorAll('[data-toggle]').forEach(sw => {
    sw.addEventListener('click', () => {
      sw.classList.toggle('ak-on');
      const row = sw.closest('.ak-wh-row');
      const dot = row.querySelector('.ak-wh-dot');
      const isOn = sw.classList.contains('ak-on');
      dot.classList.toggle('bg-emerald-500', isOn);
      dot.classList.toggle('bg-slate-300', !isOn);
      const active = document.querySelectorAll('[data-toggle].ak-on').length;
      const lbl = document.getElementById('ak-wh-count');
      if (lbl) lbl.textContent = `${active} active endpoint${active !== 1 ? 's' : ''}`;
    });
  });
  // SDK tabs
  const codes = { curl: document.getElementById('ak-code-curl'), javascript: document.getElementById('ak-code-javascript'), python: document.getElementById('ak-code-python') };
  let activeTab = 'curl';
  document.querySelectorAll('.ak-sdk-tab').forEach(t => {
    t.addEventListener('click', () => {
      document.querySelectorAll('.ak-sdk-tab').forEach(x => x.classList.remove('ak-sdk-tab-active'));
      t.classList.add('ak-sdk-tab-active');
      activeTab = t.dataset.tab;
      Object.entries(codes).forEach(([k, el]) => el.classList.toggle('hidden', k !== activeTab));
    });
  });
  document.getElementById('ak-sdk-copy')?.addEventListener('click', () => {
    const el = codes[activeTab];
    const text = Array.from(el.querySelectorAll('.ak-code-text')).map(n => n.textContent).join('\n');
    navigator.clipboard?.writeText(text);
  });
  // Modal
  const modal = document.getElementById('ak-modal');
  const openM = () => modal?.classList.remove('hidden');
  const closeM = () => modal?.classList.add('hidden');
  document.getElementById('ak-open-modal')?.addEventListener('click', openM);
  document.getElementById('ak-new-btn')?.addEventListener('click', openM);
  document.getElementById('ak-modal-close')?.addEventListener('click', closeM);
  document.getElementById('ak-modal-cancel')?.addEventListener('click', closeM);

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: account ===== */
if (window.LINER_PAGE === 'account.html') {
document.addEventListener('DOMContentLoaded', () => {
  // Tabs
  document.querySelectorAll('.acc-tab').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.acc-tab').forEach(b => b.classList.remove('acc-tab-active'));
      btn.classList.add('acc-tab-active');
      const tab = btn.dataset.tab;
      document.querySelectorAll('.acc-pane').forEach(p => p.classList.toggle('hidden', p.dataset.pane !== tab));
    });
  });
  // Toggles (connected accounts, notifications, 2FA)
  document.querySelectorAll('.acc-toggle').forEach(t => {
    t.addEventListener('click', () => t.classList.toggle('acc-toggle-on'));
  });
  // 2FA status panel
  const tfa = document.getElementById('acc-2fa-toggle');
  tfa?.addEventListener('click', () => {
    const on = tfa.classList.contains('acc-toggle-on');
    const status = document.getElementById('acc-2fa-status');
    const extras = document.getElementById('acc-2fa-extras');
    if (on) {
      status.className = 'p-4 rounded-xl border bg-emerald-50 border-emerald-200';
      status.innerHTML = '<div class="flex items-center gap-2-5"><i data-lucide="check-circle" class="lucide-sm text-emerald-500"></i><p class="text-sm text-emerald-700 font-medium m-0">Two-factor authentication is enabled. Your account is well protected.</p></div>';
      extras.classList.remove('hidden');
    } else {
      status.className = 'p-4 rounded-xl border bg-slate-50 border-slate-200';
      status.innerHTML = '<p class="text-sm text-slate-600 m-0">Enable 2FA to protect your account with an authenticator app like Google Authenticator or Authy.</p>';
      extras.classList.add('hidden');
    }
    window.lucide && lucide.createIcons();
  });
  // Password toggles
  document.querySelectorAll('.acc-pwd-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.parentElement.querySelector('input');
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.querySelector('[data-lucide]').setAttribute('data-lucide', show ? 'eye-off' : 'eye');
      window.lucide && lucide.createIcons();
    });
  });
  // Password strength
  const npwd = document.getElementById('acc-new-pwd');
  const strBox = document.getElementById('acc-strength');
  const strLbl = document.getElementById('acc-strength-label');
  npwd?.addEventListener('input', e => {
    const v = e.target.value;
    let s = 0;
    if (v.length >= 8) s++;
    if (/[A-Z]/.test(v)) s++;
    if (/[0-9]/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    if (s === 0) { strBox.classList.add('hidden'); return; }
    strBox.classList.remove('hidden');
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['', '#ef4444', '#f59e0b', '#facc15', '#10b981'];
    const texts = ['', 'text-red-500', 'text-amber-500', 'text-yellow-500', 'text-emerald-500'];
    strLbl.className = 'text-xs font-semibold ' + texts[s];
    strLbl.textContent = labels[s];
    document.querySelectorAll('.acc-strength-bar').forEach((bar, i) => {
      bar.style.background = i < s ? colors[s] : '#e2e8f0';
    });
  });
  // Frequency
  document.querySelectorAll('.acc-freq-btn').forEach(b => {
    b.addEventListener('click', () => {
      document.querySelectorAll('.acc-freq-btn').forEach(x => x.classList.remove('acc-freq-active'));
      b.classList.add('acc-freq-active');
    });
  });

  window.lucide && lucide.createIcons();
});

}

/* ===== Page: user-profile (markup inline) ===== */
if (window.LINER_PAGE === 'user-profile.html') {
document.addEventListener('DOMContentLoaded', () => {
  // Tab switching
  document.querySelectorAll('[data-up-tab]').forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.dataset.upTab;
      document.querySelectorAll('[data-up-tab]').forEach(b => b.classList.toggle('up-tab-active', b === btn));
      document.querySelectorAll('[data-up-pane]').forEach(p => p.classList.toggle('hidden', p.dataset.upPane !== tab));
    });
  });

  // Follow toggle
  document.querySelectorAll('[data-follow-btn]').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('[data-follower]');
      const isFollowing = card.dataset.following === 'true';
      const next = !isFollowing;
      card.dataset.following = next;
      btn.classList.toggle('up-follow-following', next);
      btn.classList.toggle('up-follow-not', !next);
      btn.innerHTML = next ? 'Following' : '<i data-lucide="user-plus" class="lucide-xs"></i>Follow';
      window.lucide && lucide.createIcons();
      renderFriends();
    });
  });

  // Build Friends tab from following=true followers
  function renderFriends() {
    const grid = document.getElementById('up-friends-grid');
    if (!grid) return;
    const cards = document.querySelectorAll('[data-follower][data-following="true"]');
    grid.innerHTML = Array.from(cards).map(c => {
      const img = c.querySelector('img').src;
      const name = c.querySelector('h4').textContent;
      const role = c.querySelectorAll('p')[0].textContent;
      const mutuals = c.querySelectorAll('p')[1].textContent;
      return `<div class="card p-4 flex items-center gap-4 up-friend">
        <img src="${img}" alt="${name}" class="up-friend-avatar">
        <div class="flex-1 min-w-0">
          <h4 class="font-semibold text-slate-800 text-sm truncate m-0">${name}</h4>
          <p class="text-xs text-slate-500 m-0">${role}</p>
          <p class="text-[11px] text-slate-400 mt-0-5 m-0">${mutuals}</p>
        </div>
        <div class="flex flex-col gap-1-5">
          <button class="btn-outline btn-sm"><i data-lucide="message-square" class="lucide-xs"></i></button>
          <button class="btn-outline btn-sm"><i data-lucide="more-horizontal" class="lucide-xs"></i></button>
        </div>
      </div>`;
    }).join('');
    window.lucide && lucide.createIcons();
  }
  renderFriends();

  window.lucide && lucide.createIcons();
});

}
