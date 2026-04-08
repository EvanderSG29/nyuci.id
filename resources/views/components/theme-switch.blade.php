<button
    type="button"
    @click="toggleSimpleTheme()"
    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-[var(--bg-elevated)] transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[var(--accent)] focus:ring-offset-2 focus:ring-offset-[var(--bg-card)] dark:bg-[var(--border-soft)]"
    role="switch"
    :aria-checked="resolvedTheme === 'dark' ? 'true' : 'false'"
    :aria-label="resolvedTheme === 'dark' ? 'Aktifkan light mode' : 'Aktifkan dark mode'"
>
    <span class="sr-only">Use setting</span>
    <span
        class="relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-500 ease-in-out dark:bg-[var(--bg-card)]"
        :class="resolvedTheme === 'dark' ? 'translate-x-5' : 'translate-x-0'"
    >
        <span
            class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity duration-500 ease-in"
            :class="resolvedTheme === 'dark' ? 'opacity-0 duration-100 ease-out' : 'opacity-100'"
            aria-hidden="true"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[var(--accent)]" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                <path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7"></path>
            </svg>
        </span>
        <span
            class="absolute inset-0 flex h-full w-full items-center justify-center opacity-0 transition-opacity duration-100 ease-out"
            :class="resolvedTheme === 'dark' ? 'opacity-100 duration-200 ease-in' : 'opacity-0'"
            aria-hidden="true"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[var(--accent)]" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"></path>
            </svg>
        </span>
    </span>
</button>
