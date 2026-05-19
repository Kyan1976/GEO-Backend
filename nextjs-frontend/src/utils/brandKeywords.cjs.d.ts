export function normalizeList(value: unknown): unknown[];
export function dedupeTerms(terms: unknown[]): string[];
export function isModelLikeProductTerm(term: unknown): boolean;
export function compactTerm(term: unknown): string;
export function buildBrandKeywords(options?: {
  rowBrand?: unknown;
  projectName?: unknown;
  aliases?: unknown;
  primaryKeywords?: unknown;
  rowBrandKeywords?: unknown;
}): string[];
