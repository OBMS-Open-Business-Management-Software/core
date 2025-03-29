$primary: {{ config('theme.primary', '#040E29') }};
$secondary: {{ config('theme.secondary', '#FF6B00') }};
$success: {{ config('theme.success', '#0F7038') }};
$warning: {{ config('theme.warning', '#FFB800') }};
$danger: {{ config('theme.danger', '#B21E35') }};
$info: {{ config('theme.info', '#1464F6') }};
$dark: $secondary;
$white: {{ config('theme.white', '#FFFFFF') }};
$text-muted: {{ config('theme.body', '#3C4858') }};
$body-color: {{ config('theme.body', '#3C4858') }};
$gray: {{ config('theme.gray', '#F3F9FC') }};
$border-radius: 0.25rem;
$modal-content-border-radius: 0.25rem;
$modal-content-border-width: 0;
$input-disabled-bg: $gray;

body {
    --primary: #{$primary};
    --secondary: #{$secondary};
    --success: #{$success};
    --warning: #{$warning};
    --danger: #{$danger};
    --gray: #{$gray};
    --white: #{$white};
    --input-disabled-bg: var(#{$input-disabled-bg}, var(--gray));
    --modal-content-border-radius: #{$modal-content-border-radius};
}

@import "app";
