<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="fixed left-0 top-0 h-screen w-72 bg-slate-950 border-r border-slate-800 flex flex-col">

    <!-- Logo -->
    <div class="px-8 py-7 border-b border-slate-800">

        <a href="index.php" class="flex items-center gap-3">

            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 via-cyan-500 to-emerald-400 flex items-center justify-center shadow-lg">

                <span class="text-white text-2xl font-black">
                    W
                </span>

            </div>

            <div>

                <h1 class="text-white text-2xl font-bold tracking-wide">

                    Writemize

                </h1>

                <p class="text-slate-400 text-sm">

                    Autonomous AI Team

                </p>

            </div>

        </a>

    </div>



    <!-- Navigation -->

    <nav class="flex-1 overflow-y-auto px-5 py-6 space-y-2">



        <a href="index.php"

           class="flex items-center justify-between rounded-xl px-4 py-3 transition hover:bg-slate-800 <?= $currentPage=='index.php' ? 'bg-slate-800 border-l-4 border-cyan-400' : '' ?>">

            <span class="flex items-center gap-3 text-white">

                🏠 Dashboard

            </span>

        </a>



        <a href="#"

           class="flex items-center justify-between rounded-xl px-4 py-3 bg-slate-900">

            <span class="flex items-center gap-3 text-white">

                🔍 Scout

            </span>

            <span

                id="status-scout"

                class="px-2 py-1 rounded-full bg-slate-700 text-xs text-slate-300">

                Idle

            </span>

        </a>



        <a href="#"

           class="flex items-center justify-between rounded-xl px-4 py-3 bg-slate-900">

            <span class="flex items-center gap-3 text-white">

                📈 Radar

            </span>

            <span

                id="status-radar"

                class="px-2 py-1 rounded-full bg-slate-700 text-xs text-slate-300">

                Idle

            </span>

        </a>



        <a href="#"

           class="flex items-center justify-between rounded-xl px-4 py-3 bg-slate-900">

            <span class="flex items-center gap-3 text-white">

                ✍️ Quill

            </span>

            <span

                id="status-quill"

                class="px-2 py-1 rounded-full bg-slate-700 text-xs text-slate-300">

                Idle

            </span>

        </a>



        <a href="#"

           class="flex items-center justify-between rounded-xl px-4 py-3 bg-slate-900">

            <span class="flex items-center gap-3 text-white">

                🛡️ Warden

            </span>

            <span

                id="status-warden"

                class="px-2 py-1 rounded-full bg-slate-700 text-xs text-slate-300">

                Idle

            </span>

        </a>



        <a href="#"

           class="flex items-center justify-between rounded-xl px-4 py-3 bg-slate-900">

            <span class="flex items-center gap-3 text-white">

                🚀 Pulse

            </span>

            <span

                id="status-pulse"

                class="px-2 py-1 rounded-full bg-slate-700 text-xs text-slate-300">

                Idle

            </span>

        </a>



        <!-- Pipeline -->

        <div class="mt-8 rounded-2xl bg-slate-900 p-5">

            <h3 class="text-white font-semibold mb-4">

                ⚡ Pipeline Status

            </h3>

            <div class="w-full h-3 rounded-full bg-slate-700 overflow-hidden">

                <div

                    id="pipelineProgress"

                    class="h-full w-0 bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 transition-all duration-700">

                </div>

            </div>

            <div

                id="pipelinePercent"

                class="mt-3 text-center text-slate-400 text-sm">

                0%

            </div>

        </div>



        <!-- Agent Status -->

        <div class="mt-8 rounded-2xl bg-slate-900 p-5">

            <h3 class="text-white font-semibold mb-4">

                ⚙ Agent Status

            </h3>

            <div class="space-y-3 text-sm">

                <div id="agent1" class="text-slate-400">

                    ○ Scout Pending

                </div>

                <div id="agent2" class="text-slate-400">

                    ○ Radar Pending

                </div>

                <div id="agent3" class="text-slate-400">

                    ○ Quill Pending

                </div>

                <div id="agent4" class="text-slate-400">

                    ○ Warden Pending

                </div>

                <div id="agent5" class="text-slate-400">

                    ○ Pulse Pending

                </div>

            </div>

        </div>

    </nav>



    <!-- User -->

    <div class="border-t border-slate-800 p-5">

        <div class="flex items-center gap-3">

            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center text-white font-bold">

                <?= strtoupper(substr($userName,0,1)); ?>

            </div>

            <div>

                <div class="text-white font-semibold">

                    <?= e($userName); ?>

                </div>

                <div class="text-slate-400 text-sm truncate">

                    <?= e($userEmail); ?>

                </div>

            </div>

        </div>



        <a

            href="../logout.php"

            class="mt-5 flex justify-center items-center rounded-xl bg-red-600 hover:bg-red-700 transition py-3 text-white font-semibold">

            Logout

        </a>

    </div>

</aside>