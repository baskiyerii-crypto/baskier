<?php

namespace App\Services;

use App\Domain\OrderStatus;
use App\Models\Message;
use App\Models\OohInventory;
use App\Models\OohInventoryClaim;
use App\Models\OohOccupancy;
use App\Models\OohRepresentation;
use App\Models\OohVendorRequest;
use App\Models\Order;
use App\Models\OrderQuestion;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Models\QuoteRequest;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCategoryRequest;
use App\Models\VendorDocument;
use App\Models\VendorProfileChangeRequest;
use Illuminate\Support\Facades\Schema;

class PanelNavBadgeService
{
    /**
     * Queue counts for sidebars. These stay until the underlying row is resolved;
     * marking a notification as read does not clear them.
     *
     * @return array<string, int>
     */
    public function forUser(?User $user): array
    {
        $empty = $this->empty();
        if (! $user) {
            return $empty;
        }

        try {
            if ($user->isAdmin()) {
                return array_merge($empty, $this->admin());
            }
            if ($user->isVendor()) {
                return array_merge($empty, $this->vendor($user));
            }

            return array_merge($empty, $this->customer($user));
        } catch (\Throwable) {
            return $empty;
        }
    }

    /**
     * @return array<string, int>
     */
    private function empty(): array
    {
        return [
            'admin_products' => 0,
            'admin_categories' => 0,
            'admin_documents' => 0,
            'admin_payouts' => 0,
            'admin_outdoor_review' => 0,
            'admin_support' => 0,
            'admin_vendor_updates' => 0,
            'admin_group_approvals' => 0,
            'admin_group_people' => 0,
            'admin_group_catalog' => 0,
            'admin_group_ops' => 0,
            'vendor_orders' => 0,
            'vendor_proof' => 0,
            'vendor_order_questions' => 0,
            'vendor_quotes' => 0,
            'vendor_freelancer' => 0,
            'vendor_documents' => 0,
            'vendor_modules' => 0,
            'vendor_messages' => 0,
            'vendor_product_questions' => 0,
            'vendor_group_work' => 0,
            'vendor_group_account' => 0,
            'vendor_group_finance' => 0,
            'outdoor_requests' => 0,
            'outdoor_invites' => 0,
            'outdoor_jobs' => 0,
            'outdoor_documents' => 0,
            'outdoor_modules' => 0,
            'outdoor_claims' => 0,
            'customer_orders' => 0,
            'customer_quotes' => 0,
            'customer_messages' => 0,
            'customer_questions' => 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function admin(): array
    {
        $products = Schema::hasColumn('products', 'moderation_status')
            ? Product::query()->where('moderation_status', 'pending')->count()
            : 0;
        $categories = Schema::hasTable('vendor_category_requests')
            ? VendorCategoryRequest::query()->where('status', 'pending')->count()
            : 0;
        $documents = Schema::hasTable('vendor_documents')
            ? VendorDocument::query()->where('status', 'pending')->count()
            : 0;
        $payouts = Schema::hasTable('payout_requests')
            ? PayoutRequest::query()->where('status', 'pending')->count()
            : 0;
        $outdoor = Schema::hasTable('ooh_inventories')
            ? OohInventory::query()->where('status', OohInventory::STATUS_PENDING_REVIEW)->count()
            : 0;
        $support = Schema::hasTable('support_tickets')
            ? SupportTicket::query()->whereIn('status', ['open', 'pending'])->count()
            : 0;
        $updates = Schema::hasTable('vendor_profile_change_requests')
            ? VendorProfileChangeRequest::query()->where('status', 'pending')->count()
            : 0;

        $approvals = $products + $categories + $documents;
        $people = $documents + $updates + $categories;
        $catalog = $products + $outdoor;
        $ops = $payouts + $support;

        return [
            'admin_products' => $products,
            'admin_categories' => $categories,
            'admin_documents' => $documents,
            'admin_payouts' => $payouts,
            'admin_outdoor_review' => $outdoor,
            'admin_support' => $support,
            'admin_vendor_updates' => $updates,
            'admin_group_approvals' => $approvals,
            'admin_group_people' => $people,
            'admin_group_catalog' => $catalog,
            'admin_group_ops' => $ops,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function vendor(User $user): array
    {
        $vendor = $user->vendor;
        if (! $vendor) {
            return [];
        }

        $orders = Order::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', OrderStatus::CONFIRMED)
            ->count();
        $proof = Order::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', OrderStatus::DESIGN_REVIEW)
            ->count();
        $orderQuestions = Schema::hasTable('order_questions')
            ? OrderQuestion::query()
                ->whereHas('order', fn ($q) => $q->where('vendor_id', $vendor->id))
                ->where('status', OrderQuestion::STATUS_OPEN)
                ->count()
            : 0;
        $productQuestions = Schema::hasTable('product_questions')
            ? ProductQuestion::query()
                ->whereHas('product', fn ($q) => $q->where('vendor_id', $vendor->id))
                ->where('status', ProductQuestion::STATUS_OPEN)
                ->count()
            : 0;
        $quotes = 0;
        if ($vendor->hasActiveQuotesModule()) {
            $quotes = $this->openQuoteCount($vendor, 'physical');
        }
        $freelancer = 0;
        if ($vendor->hasActiveFreelancerModule()) {
            $freelancer = $this->openQuoteCount($vendor, 'freelancer');
        }
        $documents = Schema::hasTable('vendor_documents')
            ? VendorDocument::query()->where('vendor_id', $vendor->id)->where('status', 'pending')->count()
            : 0;
        $modules = $this->expiringModuleCount($vendor);
        $messages = $this->unansweredVendorThreads($vendor);
        $work = $orders + $proof + $orderQuestions;
        $account = $documents + $modules;
        $finance = $messages;

        $outdoor = $this->outdoor($vendor, $user);

        return array_merge([
            'vendor_orders' => $orders,
            'vendor_proof' => $proof,
            'vendor_order_questions' => $orderQuestions,
            'vendor_quotes' => $quotes,
            'vendor_freelancer' => $freelancer,
            'vendor_documents' => $documents,
            'vendor_modules' => $modules,
            'vendor_messages' => $messages,
            'vendor_product_questions' => $productQuestions,
            'vendor_group_work' => $work,
            'vendor_group_account' => $account,
            'vendor_group_finance' => $finance,
        ], $outdoor);
    }

    /**
     * @return array<string, int>
     */
    private function outdoor(Vendor $vendor, User $user): array
    {
        if (! $vendor->hasOutdoorTrack()) {
            return [];
        }

        $requests = Schema::hasTable('ooh_vendor_requests')
            ? OohVendorRequest::query()
                ->where('vendor_id', $vendor->id)
                ->where('status', OohVendorRequest::STATUS_PENDING)
                ->count()
            : 0;
        $invites = Schema::hasTable('ooh_representations')
            ? OohRepresentation::query()
                ->where('status', OohRepresentation::STATUS_PENDING)
                ->where(function ($q) use ($vendor) {
                    $q->where('owner_vendor_id', $vendor->id)->orWhere('agency_vendor_id', $vendor->id);
                })
                ->where('invited_by_vendor_id', '!=', $vendor->id)
                ->count()
            : 0;
        $jobs = 0;
        if (Schema::hasTable('ooh_occupancies')) {
            $jobsQuery = OohOccupancy::query()
                ->where('kind', OohOccupancy::KIND_BOOKED)
                ->whereDate('starts_on', '<=', now()->addDays(3))
                ->whereDate('ends_on', '>=', now()->toDateString())
                ->whereHas('inventory', fn ($q) => $q->where('vendor_id', $vendor->id))
                ->whereDoesntHave('proofs', fn ($q) => $q->where('is_valid', true));
            $jobs = $jobsQuery->count();
        }
        $documents = Schema::hasTable('vendor_documents')
            ? VendorDocument::query()->where('vendor_id', $vendor->id)->where('status', 'pending')->count()
            : 0;
        $modules = 0;
        if ($vendor->outdoor_expires_at && $vendor->outdoor_expires_at->isFuture() && $vendor->outdoor_expires_at->lte(now()->addDays(7))) {
            $modules = 1;
        }
        $claims = Schema::hasTable('ooh_inventory_claims')
            ? OohInventoryClaim::query()
                ->where('status', OohInventoryClaim::STATUS_PENDING)
                ->where('reporter_vendor_id', $vendor->id)
                ->count()
            : 0;

        return [
            'outdoor_requests' => $requests,
            'outdoor_invites' => $invites,
            'outdoor_jobs' => $jobs,
            'outdoor_documents' => $documents,
            'outdoor_modules' => $modules,
            'outdoor_claims' => $claims,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function customer(User $user): array
    {
        $orders = Order::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', [OrderStatus::CANCELLED, OrderStatus::COMPLETED])
            ->count();
        $quotes = Schema::hasTable('quote_requests')
            ? QuoteRequest::query()->where('user_id', $user->id)->where('status', 'open')->count()
            : 0;
        $messages = 0;
        if (Schema::hasTable('conversations') && Schema::hasTable('messages')) {
            $conversationIds = \App\Models\Conversation::query()->where('user_id', $user->id)->pluck('id');
            foreach ($conversationIds as $id) {
                $last = Message::query()->where('conversation_id', $id)->latest('id')->first();
                if ($last && $last->is_from_vendor) {
                    $messages++;
                }
            }
        }
        $questions = Schema::hasTable('order_questions')
            ? OrderQuestion::query()
                ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
                ->where('status', OrderQuestion::STATUS_OPEN)
                ->count()
            : 0;

        return [
            'customer_orders' => $orders,
            'customer_quotes' => $quotes,
            'customer_messages' => $messages,
            'customer_questions' => $questions,
        ];
    }

    private function openQuoteCount(Vendor $vendor, string $mode): int
    {
        if (! Schema::hasTable('quote_requests')) {
            return 0;
        }
        $categoryIds = $vendor->quoteCategories()->pluck('categories.id');
        $query = QuoteRequest::query()->where('status', 'open');
        if (Schema::hasColumn('quote_requests', 'request_type')) {
            if ($mode === 'freelancer') {
                $query->where('request_type', 'freelancer');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('request_type')->orWhere('request_type', '!=', 'freelancer');
                });
            }
        } elseif ($mode === 'freelancer') {
            return 0;
        }
        if ($categoryIds->isNotEmpty()) {
            $query->whereIn('category_id', $categoryIds);
        }

        return $query->count();
    }

    private function expiringModuleCount(Vendor $vendor): int
    {
        $count = 0;
        foreach (['freelancer_expires_at', 'quotes_expires_at', 'tabela_expires_at', 'ozalit_expires_at', 'outdoor_expires_at'] as $field) {
            $date = $vendor->{$field};
            if ($date && $date->isFuture() && $date->lte(now()->addDays(7))) {
                $count++;
            }
        }

        return $count;
    }

    private function unansweredVendorThreads(Vendor $vendor): int
    {
        if (! Schema::hasTable('conversations') || ! Schema::hasTable('messages')) {
            return 0;
        }

        return (int) Message::query()
            ->select('conversation_id')
            ->where('is_from_vendor', false)
            ->whereHas('conversation', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->groupBy('conversation_id')
            ->get()
            ->filter(function (Message $row) {
                $last = Message::query()->where('conversation_id', $row->conversation_id)->latest('id')->first();

                return $last && ! $last->is_from_vendor;
            })
            ->count();
    }
}
