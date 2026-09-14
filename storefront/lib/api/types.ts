export type ApiMeta = {
    current_page?: number;
    last_page?: number;
    per_page?: number;
    total?: number;
} | null;

export type Category = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    parent_id: number | null;
    requires_quote: boolean;
    delivery_days: number | null;
    children?: Category[];
};

export type Vendor = {
    id: number;
    name: string;
    slug: string;
    company_name: string | null;
    city: string | null;
    district: string | null;
    logo: string | null;
    description: string | null;
    rating_average: string | number | null;
    reviews_count: number | null;
};

export type Product = {
    id: number;
    name: string;
    slug: string;
    sku: string | null;
    main_image: string | null;
    price: string | number;
    stock: number;
    is_featured: boolean;
    short_description: string | null;
    description: string | null;
    product_type: string | null;
    pricing_type: string | null;
    price_min: string | number | null;
    price_max: string | number | null;
    category_id: number | null;
    vendor_id: number | null;
    category?: Category | null;
    vendor?: Vendor | null;
};

export type FreelancerJob = {
    id: number;
    title: string;
    category: string | null;
    budget_min: string | number | null;
    budget_max: string | number | null;
    created_at: string | null;
};

/**
 * GET /home cevabi. Dikkat: HomeContentController ApiResponse sarmalayicisini
 * kullanmiyor, duz JSON donuyor.
 */
export type HomePayload = {
    featured_categories: Category[];
    categories: Category[];
    featured_products: Product[];
    digital_products: Product[];
    freelancer_jobs: FreelancerJob[];
    freelancer_categories: Array<{ key: string; label: string; seed: string; count: number }>;
};
