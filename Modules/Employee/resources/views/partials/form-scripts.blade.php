{{-- Behaviour of every employee add/edit form ([data-employee-form]). --}}
@once
    @push('scripts')
        <script>
            window.employeeForm = {
                sync: function ($form) {
                    $form.find('[data-named-field]').each(function () {
                        var $select = $(this).find('[data-named-select]');
                        var isNew = $select.val() === '__new';
                        var $input = $(this).find('[data-named-new]').prop('hidden', !isNew).find('input');

                        $input.prop('disabled', !isNew).prop('required', isNew && $select.prop('required'));
                        if (!isNew) {
                            $input.val('');
                        }
                    });

                    var $block = $form.find('[data-login-block]');
                    var creating = $block.length > 0 && !$block.parent('[data-login-wrap]').prop('hidden') && $block.find('[name="create_user"]').prop('checked');
                    var hasEmail = $.trim($form.find('[name="email"]').val()) !== '';

                    $block.find('[data-login-fields]').prop('hidden', !creating);
                    $block.find('[data-login-hint="invite"]').prop('hidden', !hasEmail);
                    $block.find('[data-login-hint="password"]').prop('hidden', hasEmail);
                    $block.find('[name="user_role"]').prop('disabled', !creating);
                    $block.find('[name="user_password"]').prop('disabled', !creating).prop('required', creating && !hasEmail);
                    // The new login is the linked account.
                    $form.find('[name="user_id"]').prop('disabled', creating);
                    $form.find('[data-linked-user-note]').prop('hidden', !creating);
                }
            };

            $(function () {
                $(document).on('change input', '[data-employee-form] [data-named-select], [data-employee-form] [name="create_user"], [data-employee-form] [name="email"]', function () {
                    window.employeeForm.sync($(this).closest('[data-employee-form]'));
                });

                $('[data-employee-form]').each(function () {
                    window.employeeForm.sync($(this));
                });
            });
        </script>
    @endpush
@endonce
