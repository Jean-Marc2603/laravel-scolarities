<button type="button" @click="$store.theme.toggle()"
    :aria-label="$store.theme.dark ? 'Passer au mode clair' : 'Passer au mode sombre'"
    :title="$store.theme.dark ? 'Passer au mode clair' : 'Passer au mode sombre'"
    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
    <svg x-show="!$store.theme.dark" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="1.8" aria-hidden="true">
        <circle cx="12" cy="12" r="4" />
        <path stroke-linecap="round"
            d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" />
    </svg>
    <svg x-show="$store.theme.dark" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M20.5 15.5A8.5 8.5 0 0 1 8.5 3.5 8.5 8.5 0 1 0 20.5 15.5Z" />
    </svg>
    <span x-text="$store.theme.dark ? 'Mode clair' : 'Mode sombre'">Mode sombre</span>
</button>