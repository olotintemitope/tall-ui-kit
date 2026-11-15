/*
 * tall-ui-kit: one entry for the whole kit. Import it once from the app's entry
 * (after nothing: Alpine and Livewire come from @livewireScripts):
 *
 *   import '../../vendor/olotintemitope/tall-ui-kit/resources/js/index.js';
 *
 * Per-feature modules can be imported on their own instead (see docs/javascript.md).
 * GSAP stays lazy: motion.js loads it with import() the first time an indicator
 * slides or a figure counts up, into its own chunks.
 */
import './kit';
import './toasts';
import './dialogs';
import './ui';
import './motion';
import './widgets';
