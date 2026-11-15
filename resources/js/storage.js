/*
 * Storage keys for kit state (sidebar collapse, focus mode, wizard drafts, privacy
 * mode). The prefix comes from <html data-ui-storage-prefix>, which x-ui.head prints
 * from config('tall-ui-kit.storage_prefix'), so an app can keep its existing keys.
 */
export function storageKey(name) {
    return (document.documentElement.dataset.uiStoragePrefix ?? 'tallui_') + name;
}
