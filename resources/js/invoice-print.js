import $ from 'jquery';

window.$ = window.jQuery = $;

$(function () {
    const $body = $('body');
    let printing = false;

    async function waitForPrintAssets() {
        if (document.fonts) {
            await document.fonts.ready;
        }

        await Promise.all($('img').map(function () {
            if (this.complete) {
                return Promise.resolve();
            }

            return new Promise((resolve) => {
                $(this).one('load error', resolve);
            });
        }).get());
    }

    async function printInvoice() {
        if (printing) {
            return;
        }

        printing = true;
        try {
            await waitForPrintAssets();
            window.print();
        } finally {
            printing = false;
        }
    }

    $(document).on('click', '[data-print-invoice]', printInvoice);

    waitForPrintAssets().then(function () {
        if ($body.attr('data-auto-print') === '1') {
            printInvoice();
        }
    });
});
