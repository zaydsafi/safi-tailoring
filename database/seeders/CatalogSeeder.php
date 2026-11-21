<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Seeds the Afghan clothing catalogue.
 *
 * Every category and product is written in English, Pashto and Persian
 * through the *_translations JSON columns, and each product ships with a
 * real photograph bundled in database/seeders/images, copied into the
 * public disk on seed so a fresh install never shows placeholders.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('products');
        Storage::disk('public')->makeDirectory('categories');

        $categories = $this->categories();
        $categoryIds = [];

        foreach ($categories as $index => $category) {
            $model = Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'name_translations' => ['ps' => $category['ps'], 'fa' => $category['fa']],
                    'description' => $category['description'],
                    'description_translations' => ['ps' => $category['description_ps'], 'fa' => $category['description_fa']],
                    'image' => $this->image('categories', $category['image']),
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );

            $categoryIds[$category['slug']] = $model->id;
        }

        $products = $this->products();

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                [
                    'category_id' => $categoryIds[$product['category']],
                    'name' => $product['name'],
                    'name_translations' => ['ps' => $product['ps'], 'fa' => $product['fa']],
                    'short_description' => $product['summary'],
                    'short_description_translations' => ['ps' => $product['summary_ps'], 'fa' => $product['summary_fa']],
                    'description' => $product['body'],
                    'description_translations' => ['ps' => $product['body_ps'], 'fa' => $product['body_fa']],
                    'type' => $product['type'],
                    'fabric' => $product['fabric'],
                    'sizes' => $product['sizes'],
                    'price' => $product['price'],
                    'sale_price' => $product['sale'] ?? null,
                    'stock' => $product['stock'],
                    'track_stock' => $product['type'] === 'ready',
                    'is_featured' => $product['featured'] ?? false,
                    'is_active' => true,
                    'image' => $this->image('products', $product['image']),
                ]
            );
        }

        $this->removeEnglishDemoCatalogue($categories, $products);
    }

    /**
     * Copy a bundled photo into the public disk and return its storage path.
     */
    private function image(string $directory, string $file): string
    {
        $path = $directory.'/'.$file;
        $disk = Storage::disk('public');
        $source = database_path('seeders/images/'.$file);

        if (! $disk->exists($path) && File::exists($source)) {
            $disk->put($path, File::get($source));
        }

        return $path;
    }

    /**
     * Remove the original English demo catalogue, which is recognisable by its
     * generated SVG placeholders. Categories and products added through the
     * admin panel are kept.
     *
     * @param  array<int, array<string, mixed>>  $categories
     * @param  array<int, array<string, mixed>>  $products
     */
    private function removeEnglishDemoCatalogue(array $categories, array $products): void
    {
        Product::query()
            ->whereNotIn('slug', array_column($products, 'slug'))
            ->where('image', 'like', '%.svg')
            ->delete();

        Category::query()
            ->whereNotIn('slug', array_column($categories, 'slug'))
            ->where('image', 'like', '%.svg')
            ->whereDoesntHave('products')
            ->delete();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function categories(): array
    {
        return [
            [
                'slug' => 'mens-perahan-tunban',
                'name' => "Men's Perahan Tunban",
                'ps' => 'د نارینه‌و پیراهن تنبان',
                'fa' => 'پیراهن تنبان مردانه',
                'description' => 'The national dress of Afghanistan: a knee-length kameez worn over wide tunban trousers, cut from breathable cotton for daily and Friday wear.',
                'description_ps' => 'د افغانستان ملي جامه: تر زنګون اوږد کېمیس چې پراخ تنبان پر سر اغوستل کیږي، د ورځې او جمعې لپاره له نرمو کاټنو څخه ګنډل کیږي.',
                'description_fa' => 'لباس ملی افغانستان: پیراهن بلند تا زانو که بالای تنبان گشاد پوشیده می‌شود و از کتان نرم برای پوشیدن روزانه و جمعه دوخته می‌شود.',
                'image' => 'cat-perahan-tunban.jpg',
            ],
            [
                'slug' => 'chapan-waistcoats',
                'name' => 'Chapan & Waistcoats',
                'ps' => 'چپن او واسکټونه',
                'fa' => 'چپن و واسکت‌ها',
                'description' => 'Hand-quilted chapans and embroidered waistcoats worn over the perahan tunban at weddings, Eid and formal gatherings.',
                'description_ps' => 'لاسي ګنډل شوي چپنونه او ګنډل شوي واسکټونه چې په واده، اختر او غونډو کې پر پیراهن تنبان اغوستل کیږي.',
                'description_fa' => 'چپن‌های دست‌دوخت و واسکت‌های گلدوزی‌شده که در عروسی، عید و محافل رسمی بالای پیراهن تنبان پوشیده می‌شوند.',
                'image' => 'cat-chapan.jpg',
            ],
            [
                'slug' => 'womens-firaq-partug',
                'name' => "Women's Firaq Partug",
                'ps' => 'د ښځو فراق پرتوګ',
                'fa' => 'فراق پرتوگ زنانه',
                'description' => "The traditional Afghan women's ensemble — a long firaq dress over a gathered partug trouser, with Kandahari and khamak detailing.",
                'description_ps' => 'د افغان ښځو دودیزه جامه — اوږد فراق پر پراخ پرتوګ باندې، د کندهاري او خامک ګنډلو سره.',
                'description_fa' => 'لباس سنتی زنان افغان — فراق بلند بالای پرتوگ چیندار، با جزئیات کنداری و خامک.',
                'image' => 'cat-firaq-partug.jpg',
            ],
            [
                'slug' => 'kochi-party-wear',
                'name' => 'Kochi Party Wear',
                'ps' => 'د کوچي پارټي جامې',
                'fa' => 'لباس مجلسی کوچی',
                'description' => 'Colourful Kochi dresses with mirror work, tilla embroidery and heavy dupattas for mehndi nights and family celebrations.',
                'description_ps' => 'رنګینې کوچي جامې د آینې کار، ټیلې ګنډلو او درنو دوپټو سره د مېندي شپو او کورنیو مراسمو لپاره.',
                'description_fa' => 'لباس‌های رنگین کوچی با کار آینه، گلدوزی تلا و دوپتای سنگین برای شب حنا و مراسم خانوادگی.',
                'image' => 'cat-kochi.jpg',
            ],
            [
                'slug' => 'bridal-luxury-wear',
                'name' => 'Bridal & Luxury Wear',
                'ps' => 'عروسي او لوکس جامې',
                'fa' => 'لباس عروسی و لوکس',
                'description' => 'Hand-made bridal firaq partug, groom chapans and walima outfits finished with gold tilla, khamak and silk lining.',
                'description_ps' => 'لاسي جوړ شوي عروسي فراق پرتوګ، د زوم چپنونه او د ولیمې جامې چې د سرو زرو ټیلې، خامک او ورېښمو سره بشپړې شوې.',
                'description_fa' => 'فراق پرتوگ عروسی دست‌دوخت، چپن داماد و لباس ولیمه که با تلا طلایی، خامک و آستر ابریشمی تمام می‌شود.',
                'image' => 'cat-bridal.jpg',
            ],
            [
                'slug' => 'khamak-embroidery',
                'name' => 'Khamak & Hand Embroidery',
                'ps' => 'خامک او لاسي ګنډل',
                'fa' => 'خامک و گلدوزی دستی',
                'description' => 'Khamak — the fine white-on-white Afghan embroidery — on shawls, veils and dress panels worked by hand in Kandahar.',
                'description_ps' => 'خامک — د افغانستان سپین پر سپین نری ګنډل — پر شالونو، پردو او جامو برخو چې په کندهار کې په لاس جوړیږي.',
                'description_fa' => 'خامک — گلدوزی نازک سفید بر سفید افغانی — روی شال، پرده و بخش‌های لباس که در قندهار با دست دوخته می‌شود.',
                'image' => 'cat-khamak.jpg',
            ],
            [
                'slug' => 'shawls-chadors',
                'name' => 'Shawls & Chadors',
                'ps' => 'شالونه او چادرونه',
                'fa' => 'شال‌ها و چادرها',
                'description' => 'Wool shawls, patu blankets and chadors that complete the Afghan outfit through the winter.',
                'description_ps' => 'وړینې شالونه، پټو او چادرونه چې په ژمي کې افغاني جامې بشپړوي.',
                'description_fa' => 'شال‌های پشمی، پتو و چادر که در زمستان لباس افغانی را کامل می‌کند.',
                'image' => 'cat-shawls.jpg',
            ],
            [
                'slug' => 'alteration-fitting',
                'name' => 'Alteration & Fitting',
                'ps' => 'د جامو سمون او اندازه',
                'fa' => 'اصلاح و اندازه لباس',
                'description' => 'Measuring, resizing and pressing for perahan tunban, firaq partug and bridal wear, done in the shop by our tailors.',
                'description_ps' => 'د پیراهن تنبان، فراق پرتوګ او عروسي جامو اندازه اخیستل، سمول او اوتول چې زموږ خیاطان په دوکان کې ترسره کوي.',
                'description_fa' => 'اندازه‌گیری، اصلاح و اتو کردن پیراهن تنبان، فراق پرتوگ و لباس عروسی که توسط خیاطان ما در دکان انجام می‌شود.',
                'image' => 'cat-alteration.jpg',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function products(): array
    {
        return [
            // ── Men's Perahan Tunban ────────────────────────────────────────
            [
                'category' => 'mens-perahan-tunban',
                'slug' => 'classic-white-perahan-tunban',
                'name' => 'Classic White Perahan Tunban',
                'ps' => 'کلاسیک سپین پیراهن تنبان',
                'fa' => 'پیراهن تنبان سفید کلاسیک',
                'summary' => 'Crisp white cotton set with a straight collar, stitched to your height and arm length.',
                'summary_ps' => 'سپین نری کاټن سیټ د سیده یخې سره، ستاسو د قد او لاس اوږدوالي له مخې ګنډل شوی.',
                'summary_fa' => 'ست سفید کتان با یقه ساده، مطابق قد و طول بازوی شما دوخته می‌شود.',
                'body' => 'The set every Afghan man needs. The kameez falls just below the knee with a straight collar and a three-button placket, and the tunban has a wide gathered waist with a drawstring. We cut it to your measurements in two to three days and press it before collection.',
                'body_ps' => 'هغه جامه چې هر افغان نارینه ورته اړتیا لري. کېمیس لږ تر زنګون لاندې راځي، سیده یخه او درې تڼیو پټۍ لري، او تنبان پراخه ټوټه شوې ملا او رسۍ لري. په دوو یا درې ورځو کې ستاسو اندازې ته ګنډل کیږي او مخکې له اخیستلو اوت کیږي.',
                'body_fa' => 'لباسی که هر مرد افغان به آن نیاز دارد. پیراهن کمی پایین‌تر از زانو با یقه ساده و سه دکمه، و تنبان با کمر گشاد چیندار و بند. در دو یا سه روز به اندازه شما دوخته و پیش از تحویل اتو می‌شود.',
                'fabric' => 'Cotton Poplin',
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'price' => 3500,
                'stock' => 0,
                'type' => 'custom',
                'featured' => true,
                'image' => 'perahan-tunban-white.jpg',
            ],
            [
                'category' => 'mens-perahan-tunban',
                'slug' => 'kandahari-beige-perahan-tunban',
                'name' => 'Kandahari Beige Perahan Tunban',
                'ps' => 'کندهاري نسواري پیراهن تنبان',
                'fa' => 'پیراهن تنبان نسواری قندهاری',
                'summary' => 'Soft Kandahari cotton in natural beige, with the wider tunban that southern men prefer.',
                'summary_ps' => 'نرم کندهاري کاټن په طبیعي نسواري رنګ کې، د پراخ تنبان سره چې سویلي نارینه یې خوښوي.',
                'summary_fa' => 'کتان نرم قندهاری به رنگ نسواری طبیعی، با تنبان گشادتر که مردان جنوب می‌پسندند.',
                'body' => 'Woven in Kandahar and stitched in our shop. The cotton softens with every wash, and the tunban is cut wider than a Kabuli set so it folds neatly for prayer. The kameez carries a simple chest pocket and no embroidery, which keeps it suitable for everyday wear.',
                'body_ps' => 'په کندهار کې اوبدل شوی او زموږ په دوکان کې ګنډل شوی. کاټن له هرې مینځلو نرمیږي، او تنبان د کابلي سیټ په پرتله پراخ دی تر څو د لمانځه لپاره ښه ټوټه شي. کېمیس ساده سینه جیب لري او ګنډل نلري، نو د ورځې لپاره مناسب دی.',
                'body_fa' => 'در قندهار بافته و در دکان ما دوخته شده. کتان با هر شست‌وشو نرم‌تر می‌شود و تنبان گشادتر از ست کابلی است تا برای نماز خوب تا شود. پیراهن جیب سینه ساده دارد و گلدوزی ندارد، پس برای پوشیدن روزانه مناسب است.',
                'fabric' => 'Kandahari Cotton',
                'sizes' => ['S', 'M', 'L', 'XL'],
                'price' => 4800,
                'stock' => 12,
                'type' => 'ready',
                'image' => 'perahan-tunban-grey.jpg',
            ],
            [
                'category' => 'mens-perahan-tunban',
                'slug' => 'eid-perahan-tunban',
                'name' => 'Eid Perahan Tunban',
                'ps' => 'د اختر پیراهن تنبان',
                'fa' => 'پیراهن تنبان عید',
                'summary' => 'Ivory cotton with a short standing collar and fine silk-thread work on the chest, for Eid mornings.',
                'summary_ps' => 'عاجي کاټن د لنډې ولاړې یخې او د ورېښمو نري کار سره پر سینه، د اختر سهار لپاره.',
                'summary_fa' => 'کتان عاجی با یقه کوتاه ایستاده و کار نازک نخ ابریشمی روی سینه، برای صبح عید.',
                'body' => 'A restrained take on festive dress: ivory cotton, a short standing collar and a narrow panel of silk-thread work across the chest. The embroidery is worked before cutting so the pattern follows the shoulder line, and the tunban is cut slightly narrower for a tidier line. Allow five to seven days for stitching.',
                'body_ps' => 'د اختر جامې ته یو سنجیده ښکار: عاجي کاټن، لنډه ولاړه یخه او د ورېښمو تار نرۍ پټه پر سینه. ګنډل مخکې له پرې کولو کیږي تر څو نقش د اوږې کرښې تعقیب کړي، او تنبان لږ تنګ دی تر څو بڼه ښه وي. د ګنډلو لپاره پنځه تر اووه ورځې وخت ونیسئ.',
                'body_fa' => 'روایتی سنجیده از لباس جشن: کتان عاجی، یقه کوتاه ایستاده و نوار باریک نخ ابریشمی روی سینه. گلدوزی پیش از برش انجام می‌شود تا نقش خط شانه را دنبال کند و تنبان کمی تنگ‌تر برای خط مرتب‌تر بریده می‌شود. برای دوخت پنج تا هفت روز وقت بگذارید.',
                'fabric' => 'Cotton with silk thread work',
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'price' => 6500,
                'stock' => 0,
                'type' => 'custom',
                'featured' => true,
                'image' => 'perahan-tunban-eid.jpg',
            ],
            [
                'category' => 'chapan-waistcoats',
                'slug' => 'afghan-wool-waistcoat',
                'name' => 'Afghan Wool Waistcoat',
                'ps' => 'افغاني وړینه واسکټ',
                'fa' => 'واسکت پشمی افغانی',
                'summary' => 'Sleeveless wool waistcoat with gold trim and five buttons, worn over the kameez all year.',
                'summary_ps' => 'بې لاسه وړینه واسکټ د سرو زرو پټې او پنځو تڼیو سره، چې ټول کال پر کېمیس اغوستل کیږي.',
                'summary_fa' => 'واسکت پشمی بی‌آستین با حاشیه طلایی و پنج دکمه، تمام سال بالای پیراهن پوشیده می‌شود.',
                'body' => 'The waistcoat is what turns a plain perahan tunban into formal dress. This one is cut from Afghan wool with a cotton back, trimmed in gold braid along the placket and pockets, and finished with five buttons. It sits close without pulling across the chest.',
                'body_ps' => 'واسکټ هغه شی دی چې ساده پیراهن تنبان رسمي جامه ګرځوي. دا یو له افغاني وړۍ جوړ شوی، شا یې کاټن، د تڼیو او جیبونو په غاړه کې د سرو زرو پټه لري او پنځه تڼۍ لري. پر سینه تنګ نه شي.',
                'body_fa' => 'واسکت چیزی است که پیراهن تنبان ساده را لباس رسمی می‌کند. این یکی از پشم افغانی با پشت کتان دوخته شده، در حاشیه دکمه‌ها و جیب‌ها نوار طلایی دارد و با پنج دکمه تمام می‌شود. روی سینه کشیده نمی‌شود.',
                'fabric' => 'Afghan wool with cotton back',
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'price' => 3200,
                'stock' => 22,
                'type' => 'ready',
                'featured' => true,
                'image' => 'waistcoat-wool.jpg',
            ],

            // ── Chapan ──────────────────────────────────────────────────────
            [
                'category' => 'chapan-waistcoats',
                'slug' => 'hand-quilted-chapan',
                'name' => 'Hand-Quilted Red Chapan',
                'ps' => 'لاسي څادر شوی سور چپن',
                'fa' => 'چپن سرخ دست‌دوخت لحاف‌دار',
                'summary' => 'The long quilted robe of the north, in deep red with gold braid at the collar.',
                'summary_ps' => 'د شمال اوږد څادر شوی چپن، په ژور سور رنګ کې د سرو زرو پټې سره پر یخه.',
                'summary_fa' => 'چپن بلند لحاف‌دار شمال، به رنگ سرخ تیره با نوار طلایی در یقه.',
                'body' => 'A chapan is judged by its quilting. Ours is stitched in long vertical channels by hand, lined in cotton and trimmed with gold braid at the collar and cuffs. Worn open over a perahan tunban at a wedding, or closed against the wind in winter.',
                'body_ps' => 'چپن د هغه څادر ګنډلو له مخې پېژندل کیږي. زموږ چپن په لاس اوږد عمودي کرښې لري، له کاټن سره استر شوی او د یخې او لاسونو په برخه کې سرو زرو پټه لري. په واده کې پر پیراهن تنبان پرانیستی اغوستل کیږي، یا په ژمي کې وتړل شي.',
                'body_fa' => 'چپن را با لحاف‌دوزی آن می‌شناسند. چپن ما با خطوط عمودی بلند دست‌دوخت است، آستر کتان دارد و در یقه و سرآستین نوار طلایی. در عروسی بالای پیراهن تنبان باز پوشیده می‌شود یا در زمستان بسته می‌شود.',
                'fabric' => 'Cotton, hand quilted with gold braid',
                'sizes' => ['M', 'L', 'XL', 'XXL'],
                'price' => 9500,
                'stock' => 0,
                'type' => 'custom',
                'featured' => true,
                'image' => 'chapan-red.jpg',
            ],
            [
                'category' => 'chapan-waistcoats',
                'slug' => 'embroidered-herati-chapan',
                'name' => 'Embroidered Herati Chapan',
                'ps' => 'هراتي ګنډل شوی چپن',
                'fa' => 'چپن گلدوزی‌شده هراتی',
                'summary' => 'Colourful hand-embroidered chapan with a silk facing, ready in three sizes.',
                'summary_ps' => 'رنګین لاسي ګنډل شوی چپن د ورېښمو پټې سره، په درېو اندازو چمتو.',
                'summary_fa' => 'چپن رنگین دست‌گلدوزی با لبه ابریشمی، آماده در سه اندازه.',
                'body' => 'Herati embroiderers work in satin stitch, one motif at a time, which is why no two chapans match. This one carries a vine pattern in red, gold and green over a deep ground, is lightly quilted, falls to mid-calf and has a deep inside pocket. A favourite for engagements and for photographs.',
                'body_ps' => 'هراتي ګنډونکي په ساټین دوخت ګنډي، یو نقش په یو وخت، نو ځکه دوه چپنونه یو شان نه وي. دا یو پر ژور رنګ سور، طلایي او شین د پاڼو نقش لري، سپک څادر شوی، تر نیمې پونډې راځي او ژور داخلي جیب لري. د نښترې او عکسونو لپاره ډېر خوښ دی.',
                'body_fa' => 'گلدوزگران هراتی با دوخت ساتن، هر نقش را جداگانه می‌دوزند، به همین دلیل دو چپن یکسان نیستند. این یکی نقش برگ به رنگ سرخ، طلایی و سبز روی زمینه تیره دارد، لحاف سبک دارد، تا نیم ساق می‌رسد و جیب داخلی عمیق دارد. برای نامزدی و عکس محبوب است.',
                'fabric' => 'Cotton with hand embroidery, silk facing',
                'sizes' => ['M', 'L', 'XL'],
                'price' => 12000,
                'stock' => 6,
                'type' => 'ready',
                'image' => 'chapan-charikari.jpg',
            ],

            // ── Women's Firaq Partug ────────────────────────────────────────
            [
                'category' => 'womens-firaq-partug',
                'slug' => 'traditional-firaq-partug-set',
                'name' => 'Traditional Firaq Partug Set',
                'ps' => 'دودیز فراق پرتوګ سیټ',
                'fa' => 'ست سنتی فراق پرتوگ',
                'summary' => 'Everyday firaq and partug in printed cotton, with a matching dupatta.',
                'summary_ps' => 'ورځنی فراق او پرتوګ په چاپي کاټن کې، د خپلې دوپټې سره.',
                'summary_fa' => 'فراق و پرتوگ روزانه به کتان چاپی، با دوپتای هم‌رنگ.',
                'body' => 'The shape Afghan women wear at home and to the bazaar: a straight firaq to the ankle, a partug gathered at the waist, and a cotton dupatta. Cut generously through the shoulder so it can be worn over a blouse, with a side pocket in the partug.',
                'body_ps' => 'هغه بڼه چې افغانې ښځې په کور او بازار کې اغوندي: سیده فراق تر ښۍ، پرتوګ چې ملا ته ټوټه شوی، او کاټن دوپټه. د اوږې په برخه کې پراخه پرې شوې تر څو پر قمیص واغوستل شي، او پرتوګ اړخ جیب لري.',
                'body_fa' => 'شکلی که زنان افغان در خانه و بازار می‌پوشند: فراق ساده تا مچ پا، پرتوگ چیندار در کمر و دوپتای کتانی. در شانه گشاد بریده شده تا بالای بلوز پوشیده شود و پرتوگ جیب کناری دارد.',
                'fabric' => 'Printed cotton',
                'sizes' => ['S', 'M', 'L', 'XL'],
                'price' => 5500,
                'stock' => 15,
                'type' => 'ready',
                'featured' => true,
                'image' => 'firaq-blue.jpg',
            ],
            [
                'category' => 'womens-firaq-partug',
                'slug' => 'kabuli-embroidered-firaq-partug',
                'name' => 'Kabuli Embroidered Firaq Partug',
                'ps' => 'کابلي ګنډل شوی فراق پرتوګ',
                'fa' => 'فراق پرتوگ گلدوزی‌شده کابلی',
                'summary' => 'Embroidered firaq with gold work at the neckline and cuffs, for visits and Eid.',
                'summary_ps' => 'ګنډل شوی فراق د سرو زرو کار سره پر یخه او لاسونو، د کتنو او اختر لپاره.',
                'summary_fa' => 'فراق گلدوزی‌شده با کار طلایی روی یقه و سرآستین، برای دیدار و عید.',
                'body' => 'Made for visiting and for Eid. The neckline carries a band of gold embroidery that continues onto the cuffs, and the dupatta is finished with the same border. Stitched to your measurements with a lined bodice so the embroidery sits flat.',
                'body_ps' => 'د کتنو او اختر لپاره جوړ شوی. یخه د سرو زرو ګنډلو یوه پټه لري چې پر لاسونو هم دوام کوي، او دوپټه همدغه پوله لري. ستاسو اندازې ته ګنډل کیږي، د بدن برخه استر لري تر څو ګنډل سم کېني.',
                'body_fa' => 'برای دیدار و عید ساخته شده. یقه نواری از گلدوزی طلایی دارد که تا سرآستین ادامه می‌یابد و دوپتا همان حاشیه را دارد. به اندازه شما دوخته می‌شود، بالاتنه آستر دارد تا گلدوزی صاف بنشیند.',
                'fabric' => 'Cotton with gold embroidery',
                'sizes' => ['S', 'M', 'L', 'XL'],
                'price' => 7800,
                'stock' => 0,
                'type' => 'custom',
                'featured' => true,
                'image' => 'firaq-green.jpg',
            ],
            [
                'category' => 'womens-firaq-partug',
                'slug' => 'emerald-silk-firaq-partug',
                'name' => 'Emerald Silk Firaq Partug',
                'ps' => 'زمردي ورېښمین فراق پرتوګ',
                'fa' => 'فراق پرتوگ ابریشمی زمردی',
                'summary' => 'Deep green silk firaq with a hand-finished hem, for engagements and parties.',
                'summary_ps' => 'ژور شین ورېښمین فراق د لاسي بشپړې پولې سره، د نښترې او غونډو لپاره.',
                'summary_fa' => 'فراق ابریشمی سبز تیره با حاشیه دست‌دوزی، برای نامزدی و مهمانی.',
                'body' => 'Silk has a weight that falls straight and catches light at the folds, which is why this firaq is kept for celebrations. It is lined in cotton so the silk does not cling, and the hem and cuffs are finished by hand. Dry clean only.',
                'body_ps' => 'ورېښم دومره درنې دی چې سیده راځي او پر ټوټو رڼا نیسي، نو ځکه دا فراق د مراسمو لپاره ساتل کیږي. له کاټن سره استر شوی تر څو ورېښم ونه نښلي، او پوله او لاسونه په لاس بشپړ شوي. یوازې وچ مینځل.',
                'body_fa' => 'ابریشم وزن دارد که صاف می‌افتد و در چین‌ها نور را می‌گیرد، به همین دلیل این فراق برای جشن نگه داشته می‌شود. آستر کتان دارد تا ابریشم نچسبد و حاشیه و سرآستین با دست تمام شده. فقط خشک‌شویی.',
                'fabric' => 'Silk, cotton lining',
                'sizes' => ['S', 'M', 'L'],
                'price' => 11500,
                'stock' => 0,
                'type' => 'custom',
                'featured' => true,
                'image' => 'firaq-silk.jpg',
            ],

            // ── Kochi Party Wear ────────────────────────────────────────────
            [
                'category' => 'kochi-party-wear',
                'slug' => 'kochi-embroidered-party-dress',
                'name' => 'Kochi Embroidered Party Dress',
                'ps' => 'د کوچي ګنډل شوې پارټي جامه',
                'fa' => 'لباس مجلسی گلدوزی‌شده کوچی',
                'summary' => 'Rose and green dress with dense gold embroidery across the yoke and a tasselled belt.',
                'summary_ps' => 'ګلابي او شینه جامه د سرو زرو ډک ګنډلو سره پر اوږه او د منګولو کمربند.',
                'summary_fa' => 'لباس گلابی و سبز با گلدوزی انبوه طلایی روی یوک و کمربند منگوله‌دار.',
                'body' => 'Kochi embroidery is worked in dense rows, so the yoke of this dress is almost solid gold thread. Rose cotton body, green sleeves, a tasselled belt at the waist and a deep back opening. Made to order in your size; allow two weeks for the embroidery.',
                'body_ps' => 'د کوچي ګنډل په ډکو کرښو کېږي، نو د دې جامې اوږه نږدې ټوله د سرو زرو تار ده. ګلابي کاټن بدن، شین لاسونه، د ملا په برخه کې د منګولو کمربند او ژور شا خلاص. ستاسو اندازې ته جوړیږي؛ د ګنډلو لپاره دوې اونۍ وخت ونیسئ.',
                'body_fa' => 'گلدوزی کوچی در ردیف‌های انبوه دوخته می‌شود، پس یوک این لباس تقریباً تمام نخ طلایی است. بدنه کتان گلابی، آستین سبز، کمربند منگوله‌دار در کمر و بازشدگی عمیق پشت. به اندازه شما دوخته می‌شود؛ برای گلدوزی دو هفته وقت بگذارید.',
                'fabric' => 'Cotton with gold thread embroidery',
                'sizes' => ['S', 'M', 'L'],
                'price' => 14500,
                'stock' => 0,
                'type' => 'custom',
                'featured' => true,
                'image' => 'kochi-mirror.jpg',
            ],
            [
                'category' => 'kochi-party-wear',
                'slug' => 'kochi-red-party-set',
                'name' => 'Kochi Red Party Set',
                'ps' => 'د کوچي سور پارټي سیټ',
                'fa' => 'ست مجلسی سرخ کوچی',
                'summary' => 'Red embroidered dress with a matching headscarf, ready in three sizes.',
                'summary_ps' => 'سره ګنډل شوې جامه د خپلې سرپټې سره، په درېو اندازو چمتو.',
                'summary_fa' => 'لباس سرخ گلدوزی‌شده با روسری هم‌رنگ، آماده در سه اندازه.',
                'body' => 'A bright set for mehndi nights and family dinners. The dress is red cotton-silk with gold embroidery along the front panel and sleeves, and the matching scarf is edged with the same work. Ready off the rack, and easy to have shortened if you prefer it at the knee.',
                'body_ps' => 'روښانه سیټ د مېندي شپو او کورنیو ډوډو لپاره. جامه سره کاټن-ورېښم ده د سرو زرو ګنډلو سره پر مخکې برخه او لاسونو، او سرپټه همدغه کار پوله لري. چمتو دی، او که تر زنګون یې خوښوئ، لنډول اسانه دي.',
                'body_fa' => 'ستی روشن برای شب حنا و شام خانوادگی. لباس کتان-ابریشم سرخ با گلدوزی طلایی روی پیش و آستین‌ها و روسری هم‌رنگ با همان حاشیه. آماده است و اگر کوتاه‌تر می‌خواهید، کوتاه کردن آسان است.',
                'fabric' => 'Cotton silk with gold embroidery',
                'sizes' => ['S', 'M', 'L'],
                'price' => 12500,
                'sale' => 11200,
                'stock' => 9,
                'type' => 'ready',
                'image' => 'kochi-velvet.jpg',
            ],

            // ── Bridal & Luxury Wear ────────────────────────────────────────
            [
                'category' => 'bridal-luxury-wear',
                'slug' => 'kandahari-bridal-firaq-partug',
                'name' => 'Kandahari Bridal Firaq Partug',
                'ps' => 'کندهاري عروسي فراق پرتوګ',
                'fa' => 'فراق پرتوگ عروسی قندهاری',
                'summary' => 'Deep green bridal firaq with gold tilla and a twelve-metre partug.',
                'summary_ps' => 'ژور شین عروسي فراق د سرو زرو ټیلې او دولس متره پرتوګ سره.',
                'summary_fa' => 'فراق عروسی سبز تیره با تلا طلایی و پرتوگ دوازده متری.',
                'body' => 'The Kandahari bridal silhouette: a green firaq embroidered in gold tilla from the chest to the hem, a partug cut from twelve metres of fabric, and a matching dupatta with a heavy border. The bodice is boned so the embroidery holds its shape through the nikkah and the walima.',
                'body_ps' => 'د کندهار عروسي بڼه: شین فراق چې له سینې تر پولې په سرو زرو ټیلې ګنډل شوی، پرتوګ چې له دولس مترو ټوکر پرې شوی، او همدغه رنګه دوپټه درنه پوله لري. بدن ته مټې ورکړل شوې تر څو ګنډل د نکاح او ولیمې په اوږدو کې خپل شکل وساتي.',
                'body_fa' => 'سیلوئت عروسی قندهاری: فراق سبز با گلدوزی تلا طلایی از سینه تا حاشیه، پرتوگ بریده از دوازده متر پارچه و دوپتای هم‌رنگ با حاشیه سنگین. بالاتنه قالب‌بندی شده تا گلدوزی در نکاح و ولیمه شکل خود را نگه دارد.',
                'fabric' => 'Silk with gold tilla embroidery',
                'sizes' => ['S', 'M', 'L'],
                'price' => 45000,
                'stock' => 0,
                'type' => 'custom',
                'featured' => true,
                'image' => 'bridal-kandahari.jpg',
            ],
            [
                'category' => 'bridal-luxury-wear',
                'slug' => 'bridal-gold-tilla-firaq',
                'name' => 'Bridal Firaq with Gold Tilla',
                'ps' => 'عروسي فراق د سرو زرو ټیلې سره',
                'fa' => 'فراق عروسی با تلا طلایی',
                'summary' => 'Hand-embroidered bridal firaq with tilla work over the bodice and a heavy dupatta.',
                'summary_ps' => 'لاسي ګنډل شوی عروسي فراق د ټیلې کار سره پر بدن او درنه دوپټه.',
                'summary_fa' => 'فراق عروسی دست‌گلدوزی با کار تلا روی بالاتنه و دوپتای سنگین.',
                'body' => 'Six weeks of hand work go into this firaq: tilla thread set in floral rows across the bodice, a border repeated at the hem, and a dupatta worked to match. It is made to your measurements with one fitting included, five days before the wedding.',
                'body_ps' => 'شپږ اونۍ لاسي کار په دې فراق کې دی: ټیلې تار په ګلدارو کرښو پر بدن، پوله پر پولې بیا تکرار، او دوپټه همدغه کار سره. ستاسو اندازې ته جوړیږي او یوه اندازه اخیستنه شامله ده، پنځه ورځې مخکې له واده.',
                'body_fa' => 'شش هفته کار دستی در این فراق است: نخ تلا در ردیف‌های گلدار روی بالاتنه، حاشیه تکرارشده در پایین و دوپتای هماهنگ. به اندازه شما دوخته می‌شود و یک پرو شامل است، پنج روز پیش از عروسی.',
                'fabric' => 'Silk with hand tilla work',
                'sizes' => ['S', 'M', 'L'],
                'price' => 52000,
                'stock' => 0,
                'type' => 'custom',
                'featured' => true,
                'image' => 'bridal-tilla.jpg',
            ],
            [
                'category' => 'bridal-luxury-wear',
                'slug' => 'groom-bridal-chapan',
                'name' => "Groom's Bridal Chapan",
                'ps' => 'د زوم عروسي چپن',
                'fa' => 'چپن عروسی داماد',
                'summary' => 'Cream chapan with fine quilting and a silk sash, made for the groom.',
                'summary_ps' => 'کریمي چپن د نري څادر او ورېښمین کمربند سره، د زوم لپاره.',
                'summary_fa' => 'چپن کریمی با لحاف نازک و شال ابریشمی، برای داماد.',
                'body' => "The groom's chapan is usually cream or light grey so it reads against the bride's green. Ours is quilted with a fine gold thread, faced in deep green silk, and comes with a folded silk sash. The cut is long enough to cover the waistcoat and falls straight from the shoulder.",
                'body_ps' => 'د زوم چپن معمولاً کریمي یا روښانه خړ وي تر څو د ناوې شین رنګ سره ښه ښکاره شي. زموږ یو په نري سرو زرو تار څادر شوی، د ژور شین ورېښمو پټه لري، او یو ټوټه شوی ورېښمین کمربند ورسره دی. پرې کول دومره اوږد دي چې واسکټ پټ کړي.',
                'body_fa' => 'چپن داماد معمولاً کریمی یا خاکستری روشن است تا با سبز لباس عروس هماهنگ باشد. چپن ما با نخ طلایی نازک لحاف‌دوزی شده، لبه ابریشمی سبز تیره دارد و یک شال ابریشمی تاشده همراه آن است.',
                'fabric' => 'Cotton silk, gold quilting',
                'sizes' => ['M', 'L', 'XL', 'XXL'],
                'price' => 22000,
                'stock' => 0,
                'type' => 'custom',
                'image' => 'chapan-groom.jpg',
            ],

            // ── Khamak & Hand Embroidery ────────────────────────────────────
            [
                'category' => 'khamak-embroidery',
                'slug' => 'khamak-embroidered-shawl',
                'name' => 'Khamak Embroidered Shawl',
                'ps' => 'خامک ګنډل شوی شال',
                'fa' => 'شال گلدوزی‌شده خامک',
                'summary' => 'Fine wool shawl with a khamak border, warm enough for a winter wedding.',
                'summary_ps' => 'نرۍ وړینه شال د خامک پولې سره، د ژمي واده لپاره ګرم.',
                'summary_fa' => 'شال پشمی نازک با حاشیه خامک، به اندازه کافی گرم برای عروسی زمستانی.',
                'body' => 'A practical way to wear khamak. The shawl is woven from fine Afghan wool and bordered with a band of hand khamak, so it works over a party dress and over a chador. Folded, it measures about a metre by two.',
                'body_ps' => 'خامک د اغوستلو یوه عملي لاره. شال له نرۍ افغاني وړۍ اوبدل شوی او په لاسي خامک پوله بند شوی، نو پر پارټي جامه او پر چادر دواړو ښه ځي. ټوټه شوی یو متر پر دوه متره دی.',
                'body_fa' => 'راهی عملی برای پوشیدن خامک. شال از پشم نازک افغانی بافته و با نوار خامک دست‌دوز حاشیه شده، پس بالای لباس مجلسی و بالای چادر خوب می‌آید. تاشده حدود یک متر در دو متر است.',
                'fabric' => 'Afghan wool with hand khamak',
                'sizes' => null,
                'price' => 7500,
                'stock' => 10,
                'type' => 'ready',
                'image' => 'khamak-shawl.jpg',
            ],
            [
                'category' => 'khamak-embroidery',
                'slug' => 'khamak-dress-panel',
                'name' => 'Khamak Dress Panel',
                'ps' => 'د خامک جامې برخه',
                'fa' => 'بخش خامک لباس',
                'summary' => 'A khamak front panel you can have stitched onto your own firaq.',
                'summary_ps' => 'د خامک مخکې برخه چې پر خپل فراق یې ګنډلای شئ.',
                'summary_fa' => 'بخش پیش خامک که می‌توانید روی فراق خود بدوزید.',
                'body' => 'Many families keep a khamak panel from a mother or grandmother and have it re-set into a new dress. We work the panel to your measurements in white thread on cotton, with the pattern chosen to match an existing piece or drawn fresh. Bring the old panel to the shop if you have one.',
                'body_ps' => 'ډېرې کورنۍ د مور یا نیا خامک برخه ساتي او په نوې جامه کې یې بیا ګنډي. موږ برخه ستاسو اندازې ته په سپین تار پر کاټن جوړوو، نقش د موجودې برخې سره سم یا نوی جوړ شوی. که پخوانۍ برخه لرئ، دوکان ته راوړئ.',
                'body_fa' => 'بسیاری خانواده‌ها بخش خامک از مادر یا مادربزرگ را نگه می‌دارند و در لباس نو دوباره می‌دوزند. ما بخش را به اندازه شما با نخ سفید روی کتان می‌سازیم، نقش مطابق قطعه موجود یا تازه طراحی شده. اگر قطعه قدیمی دارید، به دکان بیاورید.',
                'fabric' => 'Cotton with hand khamak',
                'sizes' => null,
                'price' => 9000,
                'stock' => 0,
                'type' => 'custom',
                'image' => 'khamak-panel.jpg',
            ],

            // ── Shawls & Chadors ────────────────────────────────────────────
            [
                'category' => 'shawls-chadors',
                'slug' => 'afghan-wool-patu-chador',
                'name' => 'Afghan Wool Patu & Chador',
                'ps' => 'افغاني وړینه پټو او چادر',
                'fa' => 'پتو و چادر پشمی افغانی',
                'summary' => 'Hand-woven wool patu that doubles as a chador, with a rolled edge.',
                'summary_ps' => 'لاسي اوبدل شوی وړینه پټو چې د چادر په توګه هم کاروي، د ټوټې پولې سره.',
                'summary_fa' => 'پتوی پشمی دست‌بافته که به‌عنوان چادر هم استفاده می‌شود، با حاشیه پیچیده.',
                'body' => 'The wool patu is what keeps a Kabul winter bearable: woven in one piece, heavy enough to sleep under and wide enough to wear as a chador over a coat. The edge is rolled and stitched by hand so it does not fray.',
                'body_ps' => 'وړینه پټو هغه شی دی چې د کابل ژمی آرام کوي: په یوه ټوټه اوبدل شوی، دومره درنې چې ترې ویده شي او دومره پراخ چې د کوټ پر سر چادر شي. پوله په لاس ټوټه او ګنډل شوې تر څو نه څیرې شي.',
                'body_fa' => 'پتوی پشمی چیزی است که زمستان کابل را قابل تحمل می‌کند: یک‌تکه بافته، به قدری سنگین که زیر آن خوابیده شود و به قدری گشاد که روی کت به‌عنوان چادر پوشیده شود. لبه با دست پیچ و دوخته شده تا ریش نشود.',
                'fabric' => 'Hand-woven Afghan wool',
                'sizes' => null,
                'price' => 2800,
                'stock' => 30,
                'type' => 'ready',
                'featured' => true,
                'image' => 'chador-wool.jpg',
            ],

            // ── Alteration & Fitting ────────────────────────────────────────
            [
                'category' => 'alteration-fitting',
                'slug' => 'perahan-tunban-fitting',
                'name' => 'Perahan Tunban Fitting',
                'ps' => 'د پیراهن تنبان اندازه',
                'fa' => 'اندازه پیراهن تنبان',
                'summary' => 'Shorten, take in or re-collar a perahan tunban in three days.',
                'summary_ps' => 'لنډول، تنګول یا نوی یخه د پیراهن تنبان په درېو ورځو کې.',
                'summary_fa' => 'کوتاه کردن، تنگ کردن یا یقه نو برای پیراهن تنبان در سه روز.',
                'body' => 'Bring the set to the shop and we will mark it on you. Typical work is shortening the kameez, taking in the side seams, narrowing the tunban waist or replacing a worn collar. Ready in three days; urgent work by arrangement.',
                'body_ps' => 'سیټ دوکان ته راوړئ او موږ یې پر تاسو نښه کوو. معمول کارونه: لنډول کېمیس، تنګول اړخونه، تنګول د تنبان ملا یا بدلول زړې یخې. په درېو ورځو چمتو؛ عاجل کار په موافقه.',
                'body_fa' => 'ست را به دکان بیاورید تا روی شما نشانه‌گذاری کنیم. کارهای معمول: کوتاه کردن پیراهن، تنگ کردن درزهای کناری، تنگ کردن کمر تنبان یا تعویض یقه کهنه. در سه روز آماده؛ کار فوری با هماهنگی.',
                'fabric' => null,
                'sizes' => null,
                'price' => 400,
                'stock' => 0,
                'type' => 'service',
                'image' => 'service-fitting.jpg',
            ],
            [
                'category' => 'alteration-fitting',
                'slug' => 'firaq-partug-alteration',
                'name' => 'Firaq Partug Alteration',
                'ps' => 'د فراق پرتوګ سمون',
                'fa' => 'اصلاح فراق پرتوگ',
                'summary' => 'Resize a firaq or partug, including hems and embroidery-safe seams.',
                'summary_ps' => 'د فراق یا پرتوګ اندازه سمول، د پولو او ګنډلو خوندي درزونو سره.',
                'summary_fa' => 'اصلاح اندازه فراق یا پرتوگ، شامل حاشیه و درزهای ایمن برای گلدوزی.',
                'body' => 'Firaq partug alterations need a gentler hand because the embroidery often runs across the seam. We open the side seams only, keep the original hem where possible and re-gather the partug by hand so the folds sit evenly. Four days, fabric matching included.',
                'body_ps' => 'د فراق پرتوګ سمون ډېر احتیاط غواړي ځکه ګنډل ډېری پر درز وي. موږ یوازې اړخونه خلاصوو، تر ممکنه پخوانۍ پوله ساتو او پرتوګ په لاس بیا ټوټه کوو تر څو ټوټې برابرې وي. څلور ورځې، د ټوکر برابرول هم شامل دي.',
                'body_fa' => 'اصلاح فراق پرتوگ دقت بیشتری می‌خواهد چون گلدوزی اغلب روی درز است. ما فقط درزهای کناری را باز می‌کنیم، حاشیه اصلی را تا حد امکان نگه می‌داریم و پرتوگ را با دست دوباره می‌چینیم تا چین‌ها یکسان باشند. چهار روز، با تطبیق پارچه.',
                'fabric' => null,
                'sizes' => null,
                'price' => 500,
                'stock' => 0,
                'type' => 'service',
                'image' => 'service-alteration.jpg',
            ],
        ];
    }
}
