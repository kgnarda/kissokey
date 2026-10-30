const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
const wait = ms => new Promise(resolve => setTimeout(resolve, reducedMotion.matches ? Math.min(ms, 300) : ms));
const format = (text, values) => text.replace(/\{(\w+)\}/g, (match, key) => values[key] ?? match);
const storage = {
    get(key) {
        try { return localStorage.getItem(key); } catch { return null; }
    },
    set(key, value) {
        // Storage can be blocked (private mode, strict settings); the hint simply shows again next visit.
        try { localStorage.setItem(key, value); } catch { return; }
    },
};


function initNav() {
    const nav = $('#site-nav');
    nav?.addEventListener('click', event => {
        if (event.target.closest('a') && nav.matches(':popover-open')) nav.hidePopover();
    });
}

function initLangHint() {
    const key = 'kissokey:lang-hint';
    if (storage.get(key)) return;
    for (const code of (navigator.languages?.length ? navigator.languages : [navigator.language]).map(lang => lang.slice(0, 2).toLowerCase())) {
        if (code === document.documentElement.lang) return;
        const hint = $(`[data-lang-hint="${CSS.escape(code)}"]`);
        if (!hint) continue;
        hint.hidden = false;
        $('[data-lang-hint-close]', hint).addEventListener('click', () => {
            hint.hidden = true;
            storage.set(key, '1');
        });
        return;
    }
}


//>*>*> The demo mirrors the real scoring rules; keep it in sync if the game rules change.
function initDemo(root) {
    const table = $('.table', root);
    const bottle = $('.bottle', root);
    const result = $('[data-demo-result]', root);
    const button = $('[data-demo-spin]', root);
    const seats = $$('.seat', root);
    const names = seats.map(seat => $('.seat__name', seat).textContent);
    const scores = seats.map(seat => Number($('.seat__score', seat).textContent));
    const rules = $$('[data-rule]');
    const i18n = JSON.parse(root.dataset.i18n);
    const outcomes = [['kiss', 'kiss'], ['bomb', 'kiss'], ['bomb', 'bomb'], ['kiss', 'bomb']];
    const spinMs = parseFloat(getComputedStyle(root).getPropertyValue('--spin-ms')) || 2800;
    let angle = 0, round = 0, busy = false, visible = false, timer = 0;

    const center = index => {
        const seat = $('.avatar', seats[index]).getBoundingClientRect(), box = table.getBoundingClientRect();
        return { x: seat.left + seat.width / 2 - box.left, y: seat.top + seat.height / 2 - box.top };
    };

    const spawn = (className, index, html) => {
        const el = Object.assign(document.createElement('span'), { className, innerHTML: html });
        const { x, y } = center(index);
        el.style.left = `${x}px`;
        el.style.top = `${y}px`;
        el.addEventListener('animationend', () => el.remove(), { once: true });
        table.append(el);
    };

    const setScore = (index, delta) => {
        scores[index] += delta;
        $('.seat__score', seats[index]).textContent = scores[index];
        $('.seat__score', seats[index]).animate([{ transform: 'scale(1)' }, { transform: 'scale(1.6)' }, { transform: 'scale(1)' }], { duration: 450, easing: 'ease-out' });
        spawn(`delta${delta < 0 ? ' delta--down' : ''}`, index, `${delta < 0 ? '−' : '+'}${Math.abs(delta)}`);
    };

    const impact = (index, type) => {
        seats[index].classList.add(`is-hit-${type}`);
        setTimeout(() => seats[index].classList.remove(`is-hit-${type}`), 650);
        if (type === 'bomb') spawn('burst', index, '<svg viewBox="0 0 24 24"><use href="#i-burst"/></svg>');
        else spawn('burst burst--kiss', index, '<svg viewBox="0 0 24 24"><use href="#i-heart"/></svg>'.repeat(3));
    };

    const throwAt = (from, to, type) => {
        const start = center(from), end = center(to);
        const mid = { x: (start.x + end.x) / 2 + (end.y - start.y) * .22, y: (start.y + end.y) / 2 - (end.x - start.x) * .22 };
        const fx = Object.assign(document.createElement('span'), { className: 'fx', innerHTML: `<svg viewBox="0 0 64 64"><use href="#i-${type}"/></svg>` });
        table.append(fx);
        const spinTo = type === 'bomb' ? 360 : 0;
        return fx.animate([
            { transform: `translate(${start.x}px, ${start.y}px) scale(.3)`, opacity: 0 },
            { transform: `translate(${mid.x}px, ${mid.y}px) scale(1.25) rotate(${spinTo / 2}deg)`, opacity: 1, offset: .5 },
            { transform: `translate(${end.x}px, ${end.y}px) scale(.9) rotate(${spinTo}deg)`, opacity: 1 },
        ], { duration: reducedMotion.matches ? 1 : 800, easing: 'cubic-bezier(.45, .05, .55, .95)' }).finished.then(() => {
            fx.remove();
            impact(to, type);
        });
    };

    const schedule = delay => {
        clearTimeout(timer);
        if (visible && !busy && !document.hidden && !reducedMotion.matches) timer = setTimeout(() => spin(false), delay);
    };

    async function spin(userInitiated) {
        if (busy) return;
        busy = true;
        clearTimeout(timer);
        button.disabled = true;
        $('[data-label]', button).textContent = i18n.spinning;
        result.setAttribute('aria-live', userInitiated ? 'polite' : 'off');
        rules.forEach(rule => rule.classList.remove('is-active'));
        seats.forEach(seat => seat.classList.remove('is-picked'));
        if (Math.max(...scores) > 12) scores.forEach((_, index) => $('.seat__score', seats[index]).textContent = scores[index] = 1 + Math.floor(Math.random() * 4));

        const target = Math.floor(Math.random() * seats.length);
        const current = ((angle % 360) + 360) % 360;
        angle += 1080 + ((target * 45 - current + 360) % 360) + (Math.random() * 14 - 7);
        bottle.style.setProperty('--angle', `${angle}deg`);
        await wait(spinMs + 100);

        const a = target, b = (target + seats.length / 2) % seats.length;
        seats[a].classList.add('is-picked');
        seats[b].classList.add('is-picked');
        await wait(450);

        let [choiceA, choiceB] = outcomes[round++ % outcomes.length];
        if (choiceA !== choiceB && scores[choiceA === 'kiss' ? a : b] === 0) choiceA = choiceB = 'kiss';
        await Promise.all([throwAt(a, b, choiceA), throwAt(b, a, choiceB)]);
        await wait(250);

        let message, rule;
        if (choiceA === 'kiss' && choiceB === 'kiss') {
            setScore(a, 1);
            setScore(b, 1);
            [message, rule] = [format(i18n.kk, { a: names[a], b: names[b] }), 'kiss-kiss'];
        } else if (choiceA === 'bomb' && choiceB === 'bomb') {
            [message, rule] = [format(i18n.bb, { a: names[a], b: names[b] }), 'bomb-bomb'];
        } else {
            const [bomber, kisser] = choiceA === 'bomb' ? [a, b] : [b, a];
            const stolen = scores[kisser];
            setScore(bomber, stolen);
            setScore(kisser, -stolen);
            [message, rule] = [format(i18n.bk, { a: names[bomber], b: names[kisser], n: stolen }), 'bomb-kiss'];
        }
        result.textContent = message;
        rules.find(item => item.dataset.rule === rule)?.classList.add('is-active');

        busy = false;
        button.disabled = false;
        $('[data-label]', button).textContent = i18n.spin;
        schedule(3400);
    }

    button.hidden = false;
    button.addEventListener('click', () => spin(true));
    new IntersectionObserver(([entry]) => {
        visible = entry.isIntersecting;
        visible ? schedule(900) : clearTimeout(timer);
    }, { threshold: .35 }).observe(root);
    document.addEventListener('visibilitychange', () => document.hidden ? clearTimeout(timer) : schedule(900));
}


function initGallery(root) {
    const track = $('[data-gallery-track]', root);
    const links = $$('[data-lightbox]', root);
    if (!track || !links.length) return;

    const prev = $('[data-gallery-prev]', root), next = $('[data-gallery-next]', root);
    const step = () => Math.max(track.clientWidth * .7, 260);
    let frame = 0;
    const update = () => {
        frame = 0;
        prev.disabled = track.scrollLeft < 8;
        next.disabled = track.scrollLeft + track.clientWidth > track.scrollWidth - 8;
        prev.hidden = next.hidden = track.scrollWidth <= track.clientWidth + 8;
    };
    prev.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: reducedMotion.matches ? 'auto' : 'smooth' }));
    next.addEventListener('click', () => track.scrollBy({ left: step(), behavior: reducedMotion.matches ? 'auto' : 'smooth' }));
    track.addEventListener('scroll', () => frame ||= requestAnimationFrame(update), { passive: true });
    new ResizeObserver(update).observe(track);

    const dialog = $('[data-lightbox-dialog]');
    const image = $('[data-lightbox-img]', dialog);
    let index = 0, startX = null;
    const show = i => {
        index = (i + links.length) % links.length;
        const link = links[index], alt = $('img', link).alt;
        Object.assign(image, { src: link.href, alt, width: link.dataset.width, height: link.dataset.height });
        $('[data-lightbox-caption]', dialog).textContent = alt;
        $('[data-lightbox-counter]', dialog).textContent = `${index + 1} / ${links.length}`;
    };

    links.forEach((link, i) => link.addEventListener('click', event => {
        event.preventDefault();
        show(i);
        dialog.showModal();
    }));
    $$('[data-lightbox-prev], [data-lightbox-next]', dialog).forEach(btn => btn.hidden = links.length < 2);
    $('[data-lightbox-prev]', dialog).addEventListener('click', () => show(index - 1));
    $('[data-lightbox-next]', dialog).addEventListener('click', () => show(index + 1));
    $('[data-lightbox-close]', dialog).addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => event.target === dialog && dialog.close());
    dialog.addEventListener('keydown', event => {
        if (event.key === 'ArrowLeft') show(index - 1);
        if (event.key === 'ArrowRight') show(index + 1);
    });
    dialog.addEventListener('pointerdown', event => startX = event.clientX);
    dialog.addEventListener('pointerup', event => {
        if (startX !== null && Math.abs(event.clientX - startX) > 50) show(index + (event.clientX < startX ? 1 : -1));
        startX = null;
    });
}


function initContactForm(form) {
    const i18n = JSON.parse(form.dataset.i18n);
    const status = $('[data-form-status]', form);
    const button = $('[type="submit"]', form);

    const setStatus = (ok, message) => {
        status.className = `form-status ${ok ? 'is-success' : 'is-error'}`;
        status.textContent = message;
        status.hidden = false;
    };

    const clearError = field => {
        field.removeAttribute('aria-invalid');
        const error = $(`[data-error-for="${field.name}"]`, form);
        if (error) error.hidden = true;
    };

    form.addEventListener('input', event => event.target.name && clearError(event.target));
    form.addEventListener('submit', async event => {
        event.preventDefault();
        $$('[aria-invalid]', form).forEach(clearError);
        button.disabled = true;
        $('[data-label]', button).textContent = i18n.sending;
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
            const data = await response.json();
            setStatus(data.ok, data.message);
            Object.entries(data.errors ?? {}).forEach(([name, message], i) => {
                const field = form.elements[name];
                field?.setAttribute('aria-invalid', 'true');
                Object.assign($(`[data-error-for="${name}"]`, form) ?? {}, { textContent: message, hidden: false });
                if (i === 0) field?.focus();
            });
            if (data.ok) form.reset();
        } catch {
            setStatus(false, i18n.error);
        } finally {
            button.disabled = false;
            $('[data-label]', button).textContent = i18n.submit;
        }
    });
}


initNav();
initLangHint();
$$('[data-demo]').forEach(initDemo);
$$('[data-gallery]').forEach(initGallery);
$$('[data-contact-form]').forEach(initContactForm);