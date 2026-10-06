(() => {
    const $$ = (sel) => [...document.querySelectorAll(sel)];
    const phone = window.matchMedia('(max-width: 640px)');
    const pins = $$('.pin-input');
    let active = pins[0];

    // Small status message for features that aren't built in this demo.
    function toast(msg) {
        let t = document.querySelector('.toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'toast';
            t.setAttribute('role', 'status');
            document.body.append(t);
        }
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(t._timer);
        t._timer = setTimeout(() => t.classList.remove('show'), 2800);
    }

    // On phones the keypad replaces the system keyboard.
    const syncKeyboard = () => pins.forEach((p) => (p.inputMode = phone.matches ? 'none' : 'numeric'));
    syncKeyboard();
    phone.addEventListener('change', syncKeyboard);

    pins.forEach((p) => {
        p.addEventListener('focus', () => (active = p));
        p.addEventListener('input', () => (p.value = p.value.replace(/\D/g, '').slice(0, 6)));
    });

    // Show / hide PIN
    $$('[data-toggle]').forEach((btn) =>
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.toggle);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('on', show);
            btn.setAttribute('aria-label', (show ? 'Hide ' : 'Show ') + (input.id === 'pin' ? 'PIN' : 'confirm PIN'));
        })
    );

    // On-screen keypad
    $$('.key[data-key]').forEach((key) =>
        key.addEventListener('click', () => {
            if (!active) return;
            if (key.dataset.key === 'del') active.value = active.value.slice(0, -1);
            else if (active.value.length < 6) active.value += key.dataset.key;
            const next = pins[pins.indexOf(active) + 1];
            if (active.value.length === 6 && next) {
                active = next;
                next.focus();
            }
        })
    );

    $$('[data-soon]').forEach((el) =>
        el.addEventListener('click', (e) => {
            e.preventDefault();
            toast(el.dataset.soon);
        })
    );
})();
