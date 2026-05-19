export function countKeywordOccurrences(
  text: unknown,
  keywords: unknown[],
  englishWordBoundary?: boolean
): Array<{ keyword: string; count: number }>;

export function buildKeywordRegex(
  keywords: unknown[],
  englishWordBoundary?: boolean
): RegExp | null;

export function keywordPattern(
  keyword: unknown,
  englishWordBoundary?: boolean
): string;

export function normalizeKeywords(keywords: unknown[]): string[];

export function normalizeStoredStats(
  stats: unknown[]
): Array<{ keyword: string; count: number }>;

export function compactTerm(term: unknown): string;

export function termVariants(term: unknown): string[];

export function resolveKeywordStats(options?: {
  text?: unknown;
  keywords?: unknown[];
  storedStats?: unknown[];
  englishWordBoundary?: boolean;
}): Array<{ keyword: string; count: number }>;

export function termMatches(
  text: unknown,
  term: unknown,
  englishWordBoundary?: boolean
): Array<{ start: number; end: number }>;

export function overlaps(
  a: { start: number; end: number },
  b: { start: number; end: number }
): boolean;
