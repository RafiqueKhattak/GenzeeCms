// Maps a Tool's `component` field (e.g. "AgeCalculator") to its Vue component.
// Tools not yet listed here fall back to the ComingSoon placeholder in
// CalculatorMount.vue. Add an entry here as each calculator is ported (Phase 4).
export const calculatorRegistry = {
    AgeCalculator: () => import('./AgeCalculator.vue'),
};
