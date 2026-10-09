<script>
    (function () {
        var KEY = 'pto-theme';
        var root = document.documentElement;
        var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

        function stored() {
            try {
                var value = localStorage.getItem(KEY);
                return value === 'dark' || value === 'light' ? value : null;
            } catch (e) {
                return null;
            }
        }

        function apply(theme) {
            root.setAttribute('data-theme', theme);
        }

        apply(stored() || (media && media.matches ? 'dark' : 'light'));

        if (media) {
            var onSystemChange = function (event) {
                if (!stored()) {
                    apply(event.matches ? 'dark' : 'light');
                }
            };
            if (media.addEventListener) {
                media.addEventListener('change', onSystemChange);
            } else if (media.addListener) {
                media.addListener(onSystemChange);
            }
        }

        document.addEventListener('click', function (event) {
            var toggle = event.target.closest ? event.target.closest('[data-theme-toggle]') : null;
            if (!toggle) {
                return;
            }
            event.preventDefault();
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            apply(next);
            try {
                localStorage.setItem(KEY, next);
            } catch (e) {}
        });
    })();
</script>
