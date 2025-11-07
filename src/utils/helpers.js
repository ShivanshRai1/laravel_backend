import { COMPANY_COLORS } from "./constants";

// Returns a color for a company based on its index in the list
export function getCompanyColor(index) {
    // Cycle through the COMPANY_COLORS array if there are more companies than colors
    return COMPANY_COLORS[index % COMPANY_COLORS.length];
}

// Sorts an array of quarter strings in chronological order
export function sortQuarters(quarters) {
    if (!Array.isArray(quarters)) return [];
    
    // Clone and sort the quarters array
    return quarters.slice().sort((a, b) => {
        // Helper to parse year and quarter from string
        const parse = q => {
            if (!q) return [0, 0];
            // Match formats like "CY 2023 Q2" or "2023 Q2"
            const match = String(q).trim().toUpperCase().match(/(?:CY\s*)?(\d{4})\s*Q([1-4])/);
            return match ? [parseInt(match[1], 10), parseInt(match[2], 10)] : [0, 0];
        };
        
        const [yearA, qA] = parse(a);
        const [yearB, qB] = parse(b);
        
        // Sort by year first, then by quarter
        if (yearA !== yearB) return yearA - yearB;
        return qA - qB;
    });
}

// Attempts to extract a stock ticker from a company name string
export function extractTickerFromName(companyName) {
    if (!companyName) return null;
    
    // Match ticker in parentheses at the end, e.g. "Apple Inc. (AAPL)"
    const match = String(companyName).match(/\(([A-Z]{1,5})\)$/);
    if (match) return match[1];
    
    // If the name itself is a ticker, return it
    if (/^[A-Z]{1,5}$/.test(companyName.trim())) {
        return companyName.trim();
    }
    
    // Return null if no ticker found
    return null;
}
