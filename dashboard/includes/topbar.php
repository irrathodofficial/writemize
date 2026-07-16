<!-- dashboard/includes/topbar.php -->

<header class="h-20 bg-white border-b border-slate-200 px-8 flex items-center justify-between">

    <!-- Left -->

    <div>

        <h1 class="text-3xl font-bold text-slate-900">

            Dashboard

        </h1>

        <p class="text-slate-500 mt-1">

            Welcome back,
            <span class="font-semibold text-slate-700">
                <?= e($userName); ?>
            </span>

        </p>

    </div>



    <!-- Right -->

    <div class="flex items-center gap-6">



        <!-- Live Clock -->

        <div class="bg-slate-100 rounded-xl px-5 py-3">

            <div class="text-xs text-slate-500">

                Current Time

            </div>

            <div

                id="liveClock"

                class="font-bold text-slate-800">

                --:--:--

            </div>

        </div>



        <!-- Pipeline -->

        <div

            id="pipelineStatus"

            class="rounded-xl px-5 py-3 bg-emerald-100">

            <div class="text-xs text-slate-500">

                Pipeline

            </div>

            <div class="font-bold text-emerald-700">

                READY

            </div>

        </div>



        <!-- Jobs -->

        <div class="rounded-xl bg-slate-100 px-5 py-3">

            <div class="text-xs text-slate-500">

                Today's Jobs

            </div>

            <div

                id="todayJobs"

                class="font-bold text-slate-800">

                0

            </div>

        </div>



        <!-- Blogs -->

        <div class="rounded-xl bg-slate-100 px-5 py-3">

            <div class="text-xs text-slate-500">

                Blogs

            </div>

            <div

                id="blogCount"

                class="font-bold text-slate-800">

                0

            </div>

        </div>



        <!-- Profile -->

        <div class="flex items-center gap-3">

            <div class="w-11 h-11 rounded-full bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 flex items-center justify-center text-white font-bold">

                <?= strtoupper(substr($userName,0,1)); ?>

            </div>

            <div>

                <div class="font-semibold text-slate-800">

                    <?= e($userName); ?>

                </div>

                <div class="text-xs text-slate-500">

                    <?= e($userEmail); ?>

                </div>

            </div>

        </div>

    </div>

</header>

<!-- Dashboard Stats -->

<div class="grid grid-cols-4 gap-6 px-8 mt-8">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

        <div class="text-slate-500">

            Pipeline Status

        </div>

        <div

            id="pipelineCard"

            class="mt-3 text-3xl font-bold text-emerald-600">

            READY

        </div>

    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

        <div class="text-slate-500">

            Active Jobs

        </div>

        <div

            id="activeJobs"

            class="mt-3 text-3xl font-bold">

            0

        </div>

    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

        <div class="text-slate-500">

            Generated Blogs

        </div>

        <div

            id="generatedBlogs"

            class="mt-3 text-3xl font-bold">

            0

        </div>

    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

        <div class="text-slate-500">

            Average SEO Score

        </div>

        <div

            id="seoScore"

            class="mt-3 text-3xl font-bold text-blue-600">

            98%

        </div>

    </div>

</div>

<script>

setInterval(function(){

    document.getElementById("liveClock").innerHTML=

    new Date().toLocaleTimeString();

},1000);

</script>