<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with([
            'category',
            'activeBorrows.user',
            'activeReservations.user',
        ])->withCount([
            'borrows as active_borrows_count' => fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']),
            'reservations as active_reservations_count' => fn($q) => $q->whereIn('status', ['Pending', 'Approved']),
        ]);

        // 1. Search filter: Title, Author, ISBN, Shelf location
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('author', 'like', "%{$s}%")
                  ->orWhere('isbn', 'like', "%{$s}%")
                  ->orWhere('location_shelf', 'like', "%{$s}%");
            });
        }

        // 2. Category filter (ប្រភេទ)
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        // 2.1 Subcategory filter (ប្រភេទរង)
        if ($request->filled('subcategory') && $request->subcategory !== 'all') {
            $query->where('subcategory', $request->subcategory);
        }

        // 2.2 Education Level filter (កម្រិតអប់រំ)
        if ($request->filled('education_level') && $request->education_level !== 'all') {
            $query->where('education_level', $request->education_level);
        }

        // 2.3 Subject filter (មុខវិជ្ជា)
        if ($request->filled('subject') && $request->subject !== 'all') {
            $sb = $request->subject;
            $query->where(function ($q) use ($sb) {
                $q->where('subject', $sb)
                  ->orWhere('recommended_major', 'like', "%{$sb}%")
                  ->orWhere('title', 'like', "%{$sb}%");
            });
        }

        // 2.4 Grade filter (ថ្នាក់)
        if ($request->filled('grade') && $request->grade !== 'all') {
            $gr = $request->grade;
            $query->where(function ($q) use ($gr) {
                $q->where('grade', $gr);
                if (preg_match('/(\d+)/', $gr, $matches)) {
                    $num = (int) $matches[1];
                    $q->orWhere('target_academic_year', $num);
                }
            });
        }

        // 2.5 Academic Year filter (ឆ្នាំសិក្សាគោលដៅ)
        if ($request->filled('academic_year') && !$request->filled('grade')) {
            $ay = (int) $request->academic_year;
            if ($ay > 0) {
                $query->where('target_academic_year', $ay);
            } elseif ($request->academic_year === 'all') {
                $query->where(function ($q) {
                    $q->whereNull('target_academic_year')->orWhere('target_academic_year', 0);
                });
            }
        }

        // 3. Status filter: Available (មាន), Borrowed (បានខ្ចី), Reserved (បានកក់)
        if ($request->filled('status')) {
            if ($request->status === 'available') {
                $query->where('available_copies', '>', 0);
            } elseif ($request->status === 'borrowed') {
                $query->where('available_copies', '<=', 0);
            } elseif ($request->status === 'reserved') {
                $query->whereHas('reservations', function ($q) {
                    $q->whereIn('status', ['Pending', 'Approved']);
                });
            }
        }

        // 4. Sort filter: Latest (ថ្មីៗ), Trending/Popular (ពេញនិយម), Downloads (ទាញយកច្រើន), Title A-Z (ក-អ)
        match ($request->get('sort')) {
            'trending', 'popular' => $query->orderByDesc('views_count'),
            'downloads' => $query->orderByDesc('downloads_count'),
            'title_asc' => $query->orderBy('title', 'asc'),
            'oldest' => $query->oldest('id'),
            default => $query->latest('id'),
        };

        // Counts for tabs/badges
        $counts = [
            'total' => Book::count(),
            'available' => Book::where('available_copies', '>', 0)->count(),
            'borrowed' => Book::where('available_copies', '<=', 0)->count(),
            'reserved' => Book::whereHas('reservations', fn($q) => $q->whereIn('status', ['Pending', 'Approved']))->count(),
        ];

        // 4. Bookshelf collections: Featured, Latest, Trending, Recommended
        $featuredBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
            ->where('is_featured', true)
            ->take(8)
            ->get();
        if ($featuredBooks->count() < 4) {
            $featuredBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
                ->latest('id')
                ->take(8)
                ->get();
        }

        $latestBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
            ->latest('id')
            ->take(8)
            ->get();

        $trendingBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
            ->orderByDesc('views_count')
            ->orderByDesc('downloads_count')
            ->take(8)
            ->get();

        $recommendedBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
            ->inRandomOrder()
            ->take(8)
            ->get();

        $books = $query->paginate(15)->withQueryString();
        $categories = Category::orderByRaw("CASE 
            WHEN slug = 'literature' THEN 1 
            WHEN slug = 'technology' THEN 2 
            WHEN slug = 'general-works' THEN 3 
            WHEN slug = 'geography-history' THEN 4 
            WHEN slug = 'philosophy-psychology' THEN 5 
            WHEN slug = 'religion' THEN 6 
            WHEN slug = 'languages' THEN 7 
            WHEN slug = 'business-management' THEN 8 
            WHEN slug = 'science-math' THEN 9 
            ELSE 99 END")->get();

        $filterOptions = [
            'subcategories' => ['សៀវភៅគោល', 'ឯកសារយោង', 'សារណាស្រាវជ្រាវ', 'ទស្សនាវដ្តីសិក្សា', 'ឯកសារបង្រៀន', 'ភាសាខ្មែរ', 'ភាសាបរទេស', 'ទូទៅ'],
            'education_levels' => ['បរិញ្ញាបត្រ', 'បរិញ្ញាបត្ររង', 'បរិញ្ញាបត្រជាន់ខ្ពស់', 'បណ្ឌិត', 'ស្រាវជ្រាវទូទៅ'],
            'subjects' => [
                'Software Engineering',
                'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'សន្តិសុខសាយប័រ',
                'បញ្ញាសិប្បនិម្មិត (AI)',
                'វិទ្យាសាស្ត្រទិន្នន័យ',
                'គ្រប់គ្រងពាណិជ្ជកម្ម',
                'ទីផ្សារ (Marketing)',
                'ភាពជាសហគ្រិន',
                'គណនេយ្យ',
                'ហិរញ្ញវត្ថុ',
                'សេដ្ឋកិច្ចវិទ្យា',
                'វិស្វកម្មសំណង់ស៊ីវិល',
                'វិស្វកម្មអគ្គិសនី',
                'នីតិសាស្ត្រ',
                'វិធីសាស្ត្រស្រាវជ្រាវ',
                'ភាសាអង់គ្លេស',
                'ប្រវត្តិវិទ្យា',
                'គណិតវិទ្យាឧត្តមសិក្សា',
            ],
            'grades' => ['ឆ្នាំទី ១', 'ឆ្នាំទី ២', 'ឆ្នាំទី ៣', 'ឆ្នាំទី ៤', 'អនុបណ្ឌិត'],
        ];

        $bookBundles = $this->getBookBundles();

        return view('books.index', compact('books', 'categories', 'counts', 'featuredBooks', 'latestBooks', 'trendingBooks', 'recommendedBooks', 'filterOptions', 'bookBundles'));
    }

    public function bundles()
    {
        $bookBundles = $this->getBookBundles();
        return view('books.bundles', compact('bookBundles'));
    }

    public function showBundle($bundleId)
    {
        $rules = $this->getBundleRules();

        // Alias backwards compatibility
        if ($bundleId === 'teacher-training') {
            $bundleId = 'software-engineering';
        }

        if (isset($rules[$bundleId])) {
            $rule = $rules[$bundleId];
            $books = $rule['query']()->orderByDesc('views_count')->get();
            $title = __($rule['title']);
            $count = $books->count();
            $bundle = [
                'id' => $bundleId,
                'title' => $title,
                'count' => $count,
                'count_label' => __('សៀវភៅ :count ក្បាល', ['count' => $count]),
                'bg_gradient' => $rule['bg_gradient'],
            ];
        } else {
            // Check if it's a category slug or ID
            $category = \App\Models\Category::where('slug', $bundleId)->orWhere('id', $bundleId)->first();
            if ($category) {
                $books = Book::where('category_id', $category->id)->orderByDesc('views_count')->get();
                $count = $books->count();
                $bundle = [
                    'id' => $category->slug,
                    'title' => $category->name,
                    'count' => $count,
                    'count_label' => __('សៀវភៅ :count ក្បាល', ['count' => $count]),
                    'bg_gradient' => 'from-blue-100/80 via-sky-50 to-indigo-100/50',
                ];
            } else {
                abort(404);
            }
        }

        $gradients = [
            'from-[#d7e5f3] via-[#e5eef7] to-[#eef4fa]',
            'from-[#dce5ef] via-[#e8eff6] to-[#f0f4f9]',
            'from-[#e8dbe4] via-[#f0e7ee] to-[#f7f2f6]',
            'from-[#dce8f1] via-[#e9f0f6] to-[#f1f6fa]',
            'from-[#f5e8d7] via-[#faf0e3] to-[#fcf7ee]',
            'from-[#d8f0ea] via-[#e8f7f3] to-[#f2faf7]',
        ];

        foreach ($books as $index => $book) {
            $book->card_gradient = $gradients[$index % count($gradients)];
        }

        return view('books.bundle_show', compact('bundle', 'books'));
    }

    private function getBundleRules(): array
    {
        return [
            'software-engineering' => [
                'id' => 'software-engineering',
                'title' => 'Software Engineering & Computing Systems',
                'bg_gradient' => 'from-blue-100/80 via-sky-50 to-indigo-100/50',
                'query' => function() {
                    return Book::where(function($q) {
                        $q->where('subject', 'like', '%Software Engineering%')
                          ->orWhere('recommended_major', 'like', '%Software Engineering%')
                          ->orWhere('recommended_major', 'like', '%Cybersecurity%')
                          ->orWhere('recommended_major', 'like', '%Network Engineering%')
                          ->orWhere('title', 'like', '%Software Engineering%')
                          ->orWhere('title', 'like', '%Clean Code%')
                          ->orWhere('title', 'like', '%Design Patterns%')
                          ->orWhere('title', 'like', '%Data-Intensive%')
                          ->orWhere('title', 'like', '%Algorithms%')
                          ->orWhere('title', 'like', '%Operating Systems%')
                          ->orWhere('title', 'like', '%Hacker%')
                          ->orWhere('title', 'like', '%Networking%')
                          ->orWhere('title', 'like', '%Essence of Software%');
                    })->whereHas('category', function($cq) {
                        $cq->where('slug', 'technology');
                    });
                },
                'fallback_covers' => [
                    '/uploads/covers/pdf_cover_1790014776_6ab17538e683e.jpg',
                    '/uploads/covers/pdf_cover_1790015359_6ab1777fb8cf6.jpg',
                    '/uploads/covers/pdf_cover_1790015406_6ab177ae27810.jpg',
                ],
            ],
            'ai-datascience' => [
                'id' => 'ai-datascience',
                'title' => 'AI & Data Science',
                'bg_gradient' => 'from-violet-100/80 via-purple-50 to-fuchsia-100/50',
                'query' => function() {
                    return Book::where(function($q) {
                        $q->where('recommended_major', 'like', '%Artificial Intelligence%')
                          ->orWhere('recommended_major', 'like', '%Data Science%')
                          ->orWhere('subject', 'like', '%បញ្ញាសិប្បនិម្មិត%')
                          ->orWhere('subject', 'like', '%វិទ្យាសាស្ត្រទិន្នន័យ%')
                          ->orWhere('title', 'like', '%Artificial Intelligence%')
                          ->orWhere('title', 'like', '%Data Science%')
                          ->orWhere('title', 'like', '%Database System%');
                    });
                },
                'fallback_covers' => [
                    '/uploads/covers/pdf_cover_1790014306_6ab17362a8f4e.jpg',
                    '/uploads/covers/pdf_cover_1790014207_6ab172ff97353.jpg',
                    '/uploads/covers/pdf_cover_1790014564_6ab17464f2ae4.jpg',
                ],
            ],
            'business-startup' => [
                'id' => 'business-startup',
                'title' => 'Business Leadership & Startups',
                'bg_gradient' => 'from-amber-100/80 via-orange-50 to-yellow-100/50',
                'query' => function() {
                    return Book::where(function($q) {
                        $q->whereHas('category', fn($cq) => $cq->where('slug', 'business-management'))
                          ->orWhere('recommended_major', 'like', '%Management%')
                          ->orWhere('recommended_major', 'like', '%គ្រប់គ្រង%')
                          ->orWhere('recommended_major', 'like', '%Marketing%')
                          ->orWhere('subject', 'like', '%សហគ្រិន%')
                          ->orWhere('subject', 'like', '%ពាណិជ្ជកម្ម%')
                          ->orWhere('subject', 'like', '%ទីផ្សារ%')
                          ->orWhere('title', 'like', '%Startup%')
                          ->orWhere('title', 'like', '%Zero to One%');
                    });
                },
                'fallback_covers' => [
                    '/uploads/covers/pdf_cover_1790013543_6ab17067513ee.jpg',
                    '/uploads/covers/pdf_cover_1790014165_6ab172d56db2a.jpg',
                    '/uploads/covers/pdf_cover_1790014364_6ab1739cac72a.jpg',
                ],
            ],
            'cambodian-law' => [
                'id' => 'cambodian-law',
                'title' => 'Cambodian Law & Legal Practice',
                'bg_gradient' => 'from-indigo-100/80 via-purple-50 to-slate-100/50',
                'query' => function() {
                    return Book::where(function($q) {
                        $q->whereHas('category', fn($cq) => $cq->where('slug', 'law'))
                          ->orWhere('recommended_major', 'like', '%Law%')
                          ->orWhere('recommended_major', 'like', '%នីតិសាស្ត្រ%')
                          ->orWhere('subject', 'like', '%នីតិសាស្ត្រ%')
                          ->orWhere('subject', 'like', '%ច្បាប់%')
                          ->orWhere('title', 'like', '%ក្រម%')
                          ->orWhere('title', 'like', '%ច្បាប់%');
                    });
                },
                'fallback_covers' => [
                    '/uploads/covers/pdf_cover_1790013312_6ab16f804b813.jpg',
                    '/uploads/covers/pdf_cover_1790013227_6ab16f2b555d1.jpg',
                ],
            ],
            'civil-engineering' => [
                'id' => 'civil-engineering',
                'title' => 'Civil & Structural Engineering',
                'bg_gradient' => 'from-slate-100/90 via-zinc-50 to-blue-100/60',
                'query' => function() {
                    return Book::where(function($q) {
                        $q->whereHas('category', fn($cq) => $cq->where('slug', 'engineering'))
                          ->orWhere('recommended_major', 'like', '%Civil%')
                          ->orWhere('recommended_major', 'like', '%Electrical%')
                          ->orWhere('recommended_major', 'like', '%វិស្វកម្ម%')
                          ->orWhere('subject', 'like', '%វិស្វកម្ម%');
                    });
                },
                'fallback_covers' => [
                    '/uploads/covers/pdf_cover_1790013791_6ab1715faffe8.jpg',
                    '/uploads/covers/pdf_cover_1790013689_6ab170f99ef39.jpg',
                    '/uploads/covers/pdf_cover_1790013471_6ab1701fa87d2.jpg',
                ],
            ],
            'economics-finance' => [
                'id' => 'economics-finance',
                'title' => 'Corporate Finance & Economics',
                'bg_gradient' => 'from-teal-100/80 via-emerald-50 to-cyan-100/50',
                'query' => function() {
                    return Book::where(function($q) {
                        $q->whereHas('category', fn($cq) => $cq->where('slug', 'economics-finance'))
                          ->orWhere('recommended_major', 'like', '%Economics%')
                          ->orWhere('recommended_major', 'like', '%Finance%')
                          ->orWhere('recommended_major', 'like', '%Accounting%')
                          ->orWhere('recommended_major', 'like', '%សេដ្ឋកិច្ច%')
                          ->orWhere('subject', 'like', '%ហិរញ្ញវត្ថុ%')
                          ->orWhere('subject', 'like', '%គណនេយ្យ%')
                          ->orWhere('subject', 'like', '%សេដ្ឋកិច្ច%');
                    });
                },
                'fallback_covers' => [
                    '/uploads/covers/pdf_cover_1790014064_6ab17270c6e26.jpg',
                    '/uploads/covers/pdf_cover_1790014116_6ab172a482de5.jpg',
                    '/uploads/covers/pdf_cover_1790013999_6ab1722f94cb5.jpg',
                ],
            ],
            'languages-communication' => [
                'id' => 'languages-communication',
                'title' => 'Languages & Communication',
                'bg_gradient' => 'from-rose-100/80 via-orange-50 to-pink-100/50',
                'query' => function() {
                    return Book::where(function($q) {
                        $q->whereHas('category', fn($cq) => $cq->where('slug', 'languages'))
                          ->orWhere('recommended_major', 'like', '%Language%')
                          ->orWhere('recommended_major', 'like', '%Literature%')
                          ->orWhere('recommended_major', 'like', '%ភាសា%')
                          ->orWhere('subject', 'like', '%ភាសា%')
                          ->orWhere('title', 'like', '%ភាសា%')
                          ->orWhere('title', 'like', '%HSK%')
                          ->orWhere('title', 'like', '%English%');
                    });
                },
                'fallback_covers' => [
                    '/uploads/covers/pdf_cover_1790013892_6ab171c4c1e40.jpg',
                    '/uploads/covers/pdf_cover_1790014467_6ab17403487c6.jpg',
                ],
            ],
            'science-mathematics' => [
                'id' => 'science-mathematics',
                'title' => 'Science & Higher Mathematics',
                'bg_gradient' => 'from-cyan-100/80 via-sky-50 to-blue-100/50',
                'query' => function() {
                    return Book::where(function($q) {
                        $q->whereHas('category', fn($cq) => $cq->where('slug', 'science-math'))
                          ->orWhere('subject', 'like', '%គណិត%')
                          ->orWhere('recommended_major', 'like', '%គណិត%')
                          ->orWhere('title', 'like', '%Calculus%')
                          ->orWhere('title', 'like', '%Questions%');
                    });
                },
                'fallback_covers' => [
                    '/uploads/covers/pdf_cover_1790014516_6ab1743477153.jpg',
                ],
            ],
            'thesis-research' => [
                'id' => 'thesis-research',
                'title' => 'Research & Thesis Methodology',
                'bg_gradient' => 'from-emerald-100/80 via-teal-50 to-green-100/50',
                'query' => function() {
                    return Book::where(function($q) {
                        $q->whereHas('category', fn($cq) => $cq->where('slug', 'general-works'))
                          ->orWhere('subject', 'like', '%ស្រាវជ្រាវ%')
                          ->orWhere('subcategory', 'like', '%ស្រាវជ្រាវ%')
                          ->orWhere('title', 'like', '%ស្រាវជ្រាវ%')
                          ->orWhere('title', 'like', '%Research%')
                          ->orWhere('title', 'like', '%សារណា%');
                    });
                },
                'fallback_covers' => [
                    '/uploads/covers/pdf_cover_1790013073_6ab16e917a4e5.jpg',
                    '/uploads/covers/pdf_cover_1790012908_6ab16decbd563.jpg',
                ],
            ],
        ];
    }

    private function getBookBundles(): array
    {
        $rules = $this->getBundleRules();
        $bundles = [];

        foreach ($rules as $id => $def) {
            $books = $def['query']()->orderByDesc('views_count')->get();
            $count = $books->count();

            // Only display bundles that actually have related books
            if ($count === 0) {
                continue;
            }

            $covers = $books->pluck('cover_image')->filter(fn($c) => !empty($c))->values()->all();
            if (empty($covers)) {
                $covers = $def['fallback_covers'] ?? [];
            }

            $title = __($def['title']);

            $bundles[] = [
                'id' => $id,
                'title' => $title,
                'count' => $count,
                'count_label' => __('សៀវភៅ :count ក្បាល', ['count' => $count]),
                'bg_gradient' => $def['bg_gradient'],
                'url' => route('books.bundles.show', $id),
                'type' => 'stack',
                'cover_image' => $covers[0] ?? ($def['fallback_covers'][0] ?? null),
                'extra_images' => array_slice($covers, 1, 2) ?: (array_slice($def['fallback_covers'] ?? [], 1, 2)),
            ];
        }

        return $bundles;
    }

    public function quickSearch(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (empty($q)) {
            return response()->json([]);
        }

        $books = Book::with('category')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('author', 'like', "%{$q}%")
                      ->orWhere('isbn', 'like', "%{$q}%")
                      ->orWhere('location_shelf', 'like', "%{$q}%");
            })
            ->select('id', 'title', 'author', 'isbn', 'category_id', 'cover_image', 'available_copies', 'total_copies', 'pdf_file')
            ->limit(6)
            ->get()
            ->map(function ($book) {
                $cover = $book->cover_image 
                    ? (str_starts_with($book->cover_image, 'http') ? $book->cover_image : asset(ltrim($book->cover_image, '/'))) 
                    : 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=120&auto=format&fit=crop&q=80';

                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'isbn' => $book->isbn,
                    'category' => $book->category ? __($book->category->name) : '',
                    'cover_url' => $cover,
                    'available' => $book->available_copies > 0,
                    'available_copies' => $book->available_copies,
                    'total_copies' => $book->total_copies,
                    'url' => route('books.show', $book->id),
                    'has_pdf' => !empty($book->pdf_file),
                ];
            });

        return response()->json($books);
    }

    public function show(Request $request, Book $book)
    {
        $sessionKey = 'viewed_book_' . $book->id;
        if (!session()->has($sessionKey)) {
            $book->increment('views_count');
            session()->put($sessionKey, now()->timestamp);
            $book->refresh();
        }

        $book->load([
            'category',
            'activeBorrows.user',
            'activeReservations.user',
            'borrows' => fn($q) => $q->latest('borrow_date')->with('user')->take(10),
            'reservations' => fn($q) => $q->latest('reservation_date')->with('user')->take(10),
        ])->loadCount([
            'borrows as active_borrows_count' => fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']),
            'reservations as active_reservations_count' => fn($q) => $q->whereIn('status', ['Pending', 'Approved']),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'book' => $book,
            ]);
        }

        return view('books.show', compact('book'));
    }

    public function store(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? \App\Models\User::where('role', 'admin')->first();
        if ($user && ! $user->isAdmin()) {
            return back()->with('error', __('Only library administrators can add books to the catalog.'));
        }

        // Clean empty string inputs to null so MySQL strict mode won't reject integer or unique columns
        $raw = $request->all();
        foreach (['isbn', 'category_id', 'target_academic_year', 'recommended_major', 'published_year', 'cover_image', 'pdf_cover_data', 'pdf_file', 'description'] as $field) {
            if (isset($raw[$field]) && is_string($raw[$field]) && trim($raw[$field]) === '') {
                $raw[$field] = null;
            }
        }
        $request->merge($raw);

        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'author' => 'required|string|max:191',
            'isbn' => 'nullable|string|max:191|unique:books,isbn',
            'category_id' => 'nullable|exists:categories,id',
            'target_academic_year' => 'nullable|integer|min:0|max:4',
            'recommended_major' => 'nullable|string|max:150',
            'total_copies' => 'required|integer|min:1',
            'available_copies' => 'nullable|integer|min:0',
            'location_shelf' => 'required|string|max:100',
            'published_year' => 'nullable|integer|min:1800|max:2030',
            'cover_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'pdf_upload' => 'nullable|file|mimes:pdf|max:1048576',
            'cover_image' => 'nullable|string|max:500',
            'pdf_cover_data' => 'nullable|string',
            'pdf_file' => 'nullable|string|max:500',
            'allow_pdf_download' => 'nullable',
            'description' => 'nullable|string',
        ]);

        // Normalize nullable fields to proper types/null
        $validated['isbn'] = !empty($validated['isbn']) ? trim($validated['isbn']) : null;
        $validated['target_academic_year'] = !empty($validated['target_academic_year']) ? (int) $validated['target_academic_year'] : null;
        $validated['category_id'] = !empty($validated['category_id']) ? (int) $validated['category_id'] : null;
        $validated['published_year'] = !empty($validated['published_year']) ? (int) $validated['published_year'] : null;
        $validated['pdf_file'] = !empty($validated['pdf_file']) ? trim($validated['pdf_file']) : null;
        $validated['cover_image'] = !empty($validated['cover_image']) ? trim($validated['cover_image']) : null;
        $validated['recommended_major'] = !empty($validated['recommended_major']) ? trim($validated['recommended_major']) : null;
        $validated['description'] = !empty($validated['description']) ? trim($validated['description']) : null;

        // Handle uploaded cover image
        if ($request->hasFile('cover_image_file') && $request->file('cover_image_file')->isValid()) {
            $cover = $request->file('cover_image_file');
            $coverName = 'cover_' . time() . '_' . uniqid() . '.' . $cover->getClientOriginalExtension();
            $cover->move(public_path('uploads/covers'), $coverName);
            $validated['cover_image'] = '/uploads/covers/' . $coverName;
        } elseif ($request->filled('pdf_cover_data')) {
            $data = $request->input('pdf_cover_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $rawBase64 = substr($data, strpos($data, ',') + 1);
                $ext = strtolower($type[1]) === 'jpeg' ? 'jpg' : strtolower($type[1]);
                $decoded = base64_decode($rawBase64);
                if ($decoded !== false) {
                    $coverName = 'pdf_cover_' . time() . '_' . uniqid() . '.' . $ext;
                    $destPath = public_path('uploads/covers');
                    if (!file_exists($destPath)) {
                        mkdir($destPath, 0755, true);
                    }
                    file_put_contents($destPath . '/' . $coverName, $decoded);
                    $validated['cover_image'] = '/uploads/covers/' . $coverName;
                }
            }
        }

        // Handle uploaded PDF document
        if ($request->hasFile('pdf_upload') && $request->file('pdf_upload')->isValid()) {
            $pdf = $request->file('pdf_upload');
            $pdfName = 'book_' . time() . '_' . uniqid() . '.' . $pdf->getClientOriginalExtension();
            $pdf->move(public_path('uploads/pdfs'), $pdfName);
            $validated['pdf_file'] = '/uploads/pdfs/' . $pdfName;
        }

        if (!isset($validated['available_copies']) || $validated['available_copies'] === '' || $validated['available_copies'] === null) {
            $validated['available_copies'] = $validated['total_copies'];
        }

        if (empty($validated['cover_image'])) {
            $validated['cover_image'] = 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80';
        }

        $validated['allow_pdf_download'] = $request->boolean('allow_pdf_download');

        unset($validated['cover_image_file'], $validated['pdf_upload'], $validated['pdf_cover_data']);

        $book = Book::create($validated);

        \App\Models\ActivityLog::log(
            'book_created',
            __('បានបន្ថែមសៀវភៅថ្មី ":title" ទៅក្នុងប្រព័ន្ធបណ្ណាល័យ', ['title' => $book->title]),
            $book
        );

        return redirect()->route('books.index')->with('success', __('Book added successfully!'));
    }

    public function update(Request $request, Book $book)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? \App\Models\User::where('role', 'admin')->first();
        if ($user && ! $user->isAdmin()) {
            return back()->with('error', __('Only library administrators can edit book information.'));
        }

        // Clean empty string inputs to null so MySQL strict mode won't reject integer or unique columns
        $raw = $request->all();
        foreach (['isbn', 'category_id', 'target_academic_year', 'recommended_major', 'published_year', 'cover_image', 'pdf_cover_data', 'pdf_file', 'description'] as $field) {
            if (isset($raw[$field]) && is_string($raw[$field]) && trim($raw[$field]) === '') {
                $raw[$field] = null;
            }
        }
        $request->merge($raw);

        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'author' => 'required|string|max:191',
            'isbn' => 'nullable|string|max:191|unique:books,isbn,' . $book->id,
            'category_id' => 'nullable|exists:categories,id',
            'target_academic_year' => 'nullable|integer|min:0|max:4',
            'recommended_major' => 'nullable|string|max:150',
            'total_copies' => 'required|integer|min:1',
            'available_copies' => 'required|integer|min:0',
            'location_shelf' => 'required|string|max:100',
            'published_year' => 'nullable|integer|min:1800|max:2030',
            'cover_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'pdf_upload' => 'nullable|file|mimes:pdf|max:1048576',
            'cover_image' => 'nullable|string|max:500',
            'pdf_cover_data' => 'nullable|string',
            'pdf_file' => 'nullable|string|max:500',
            'allow_pdf_download' => 'nullable',
            'description' => 'nullable|string',
        ]);

        // Normalize nullable fields to proper types/null
        $validated['isbn'] = !empty($validated['isbn']) ? trim($validated['isbn']) : null;
        $validated['target_academic_year'] = !empty($validated['target_academic_year']) ? (int) $validated['target_academic_year'] : null;
        $validated['category_id'] = !empty($validated['category_id']) ? (int) $validated['category_id'] : null;
        $validated['published_year'] = !empty($validated['published_year']) ? (int) $validated['published_year'] : null;
        $validated['pdf_file'] = !empty($validated['pdf_file']) ? trim($validated['pdf_file']) : null;
        $validated['cover_image'] = !empty($validated['cover_image']) ? trim($validated['cover_image']) : null;
        $validated['recommended_major'] = !empty($validated['recommended_major']) ? trim($validated['recommended_major']) : null;
        $validated['description'] = !empty($validated['description']) ? trim($validated['description']) : null;

        // Handle uploaded cover image
        if ($request->hasFile('cover_image_file') && $request->file('cover_image_file')->isValid()) {
            $cover = $request->file('cover_image_file');
            $coverName = 'cover_' . time() . '_' . uniqid() . '.' . $cover->getClientOriginalExtension();
            $cover->move(public_path('uploads/covers'), $coverName);
            $validated['cover_image'] = '/uploads/covers/' . $coverName;
        } elseif ($request->filled('pdf_cover_data')) {
            $data = $request->input('pdf_cover_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $rawBase64 = substr($data, strpos($data, ',') + 1);
                $ext = strtolower($type[1]) === 'jpeg' ? 'jpg' : strtolower($type[1]);
                $decoded = base64_decode($rawBase64);
                if ($decoded !== false) {
                    $coverName = 'pdf_cover_' . time() . '_' . uniqid() . '.' . $ext;
                    $destPath = public_path('uploads/covers');
                    if (!file_exists($destPath)) {
                        mkdir($destPath, 0755, true);
                    }
                    file_put_contents($destPath . '/' . $coverName, $decoded);
                    $validated['cover_image'] = '/uploads/covers/' . $coverName;
                }
            }
        }

        // Handle uploaded PDF document
        if ($request->hasFile('pdf_upload') && $request->file('pdf_upload')->isValid()) {
            $pdf = $request->file('pdf_upload');
            $pdfName = 'book_' . time() . '_' . uniqid() . '.' . $pdf->getClientOriginalExtension();
            $pdf->move(public_path('uploads/pdfs'), $pdfName);
            $validated['pdf_file'] = '/uploads/pdfs/' . $pdfName;
        }

        if (empty($validated['cover_image'])) {
            $validated['cover_image'] = $book->cover_image ?? 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80';
        }

        $validated['allow_pdf_download'] = $request->boolean('allow_pdf_download');

        unset($validated['cover_image_file'], $validated['pdf_upload'], $validated['pdf_cover_data']);

        $book->update($validated);

        if ($request->has('redirect_to') && $request->redirect_to === 'show') {
            return redirect()->route('books.show', $book)->with('success', __('Book updated successfully!'));
        }

        return redirect()->route('books.index')->with('success', __('Book updated successfully!'));
    }

    public function extractCover(Request $request, Book $book)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? \App\Models\User::where('role', 'admin')->first();
        if ($user && ! $user->isAdmin()) {
            return response()->json(['success' => false, 'message' => __('Unauthorized')], 403);
        }

        $data = $request->input('cover_data') ?? $request->input('pdf_cover_data');
        if (!$data || !is_string($data)) {
            return response()->json(['success' => false, 'message' => __('Cover data is required')], 422);
        }
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $rawBase64 = substr($data, strpos($data, ',') + 1);
            $ext = strtolower($type[1]) === 'jpeg' ? 'jpg' : strtolower($type[1]);
            $decoded = base64_decode($rawBase64);
            if ($decoded !== false) {
                $coverName = 'pdf_cover_' . time() . '_' . uniqid() . '.' . $ext;
                $destPath = public_path('uploads/covers');
                if (!file_exists($destPath)) {
                    mkdir($destPath, 0755, true);
                }
                file_put_contents($destPath . '/' . $coverName, $decoded);
                $book->update([
                    'cover_image' => '/uploads/covers/' . $coverName,
                ]);

                return response()->json([
                    'success' => true,
                    'cover_url' => '/uploads/covers/' . $coverName,
                    'message' => __('Cover extracted and saved successfully!'),
                ]);
            }
        }

        return response()->json(['success' => false, 'message' => __('Invalid image data')], 422);
    }

    public function destroy(Book $book)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? \App\Models\User::where('role', 'admin')->first();
        if ($user && ! $user->canDeleteBooks()) {
            return back()->with('error', __('Only the Library Manager or Super Admin can delete books from the system.'));
        }

        if ($book->activeBorrows()->exists()) {
            return redirect()->route('books.index')->with('error', __('Cannot delete this book because it currently has active loans. Please return all copies first.'));
        }

        $title = $book->title;

        // Cancel any active reservations for this book
        $book->activeReservations()->update([
            'status' => 'Cancelled',
            'notes' => __('Book removed from library collection'),
        ]);

        $book->delete();

        \App\Models\ActivityLog::log(
            'book_deleted',
            __('បានលុបសៀវភៅ ":title" ចេញពីប្រព័ន្ធបណ្ណាល័យ', ['title' => $title]),
            null
        );

        return redirect()->route('books.index')->with('success', __('Book ":title" deleted successfully!', ['title' => $title]));
    }

    /**
     * Record real view count via AJAX when previewing or opening book details
     */
    public function recordView(Request $request, Book $book)
    {
        $sessionKey = 'viewed_book_' . $book->id;
        $incremented = false;

        $lastViewed = session()->get($sessionKey);
        if (!$lastViewed || (now()->timestamp - $lastViewed) > 7200) {
            $book->increment('views_count');
            session()->put($sessionKey, now()->timestamp);
            $book->refresh();
            $incremented = true;
        }

        return response()->json([
            'success' => true,
            'incremented' => $incremented,
            'book_id' => $book->id,
            'views_count' => (int) $book->views_count,
        ]);
    }

    /**
     * Track and download PDF file
     */
    public function downloadPdf(Book $book)
    {
        if (!$book->allow_pdf_download || empty($book->pdf_file)) {
            return back()->with('error', __('This book cannot be downloaded.'));
        }

        $sessionKey = 'downloaded_book_' . $book->id;
        if (!session()->has($sessionKey)) {
            $book->increment('downloads_count');
            session()->put($sessionKey, now()->timestamp);
            $book->refresh();
        }

        $pdfPath = public_path(ltrim($book->pdf_file, '/'));
        if (file_exists($pdfPath)) {
            $downloadName = \Illuminate\Support\Str::slug($book->title) . '.pdf';
            return response()->download($pdfPath, $downloadName);
        }

        if (str_starts_with($book->pdf_file, 'http://') || str_starts_with($book->pdf_file, 'https://')) {
            return redirect($book->pdf_file);
        }

        return back()->with('error', __('PDF file not found on the server.'));
    }

    /**
     * In-App PDF Reader (Self-Service Reading)
     */
    public function readPdf(Book $book)
    {
        $sessionKey = 'read_book_' . $book->id;
        if (!session()->has($sessionKey)) {
            $book->increment('views_count');
            session()->put($sessionKey, now()->timestamp);
            $book->refresh();
        }
        $pdfUrl = $book->pdf_file ?: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
        if (!str_starts_with($pdfUrl, 'http://') && !str_starts_with($pdfUrl, 'https://')) {
            $pdfUrl = asset(ltrim($pdfUrl, '/'));
        }
        return view('books.reader', compact('book', 'pdfUrl'));
    }
}
