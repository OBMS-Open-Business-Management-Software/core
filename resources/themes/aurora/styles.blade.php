$primary: {{ config('theme.primary', '#040E29') }};
$secondary: {{ config('theme.secondary', '#3C4858') }};
$secondary-dark: {{ config('theme.secondary-dark', '#2B3747') }};
$dark: $secondary;
$white: {{ config('theme.white', '#FFFFFF') }};
$text-muted: $secondary;
$body-color: $secondary;
$gray: {{ config('theme.gray', '#F3F9FC') }};
$border-radius: 0.2rem;
$modal-content-border-radius: 0.2rem;
$modal-content-border-width: 0;
$input-disabled-bg: $gray;

body {
    --primary: #{$primary};
    --secondary: #{$secondary};
    --secondary-dark: #{$secondary-dark};
    --gray: #{$gray};
    --white: #{$white};
    --input-disabled-bg: var(#{$input-disabled-bg}, var(--gray));
    --modal-content-border-radius: #{$modal-content-border-radius};
}

@import "app";
