<div class="flex flex-col items-center">
    <!-- Node Card -->
    <div class="w-64 bg-white rounded-xl border-2 {{ count($node['children']) > 0 ? 'border-red-500 shadow-md' : 'border-slate-200 shadow-sm' }} p-4 text-center relative hover:shadow-lg transition">
        <!-- Avatar / Photo -->
        <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-700 text-sm overflow-hidden mb-2 border border-slate-200">
            @if(!empty($node['photo']))
                <img src="{{ asset('storage/' . $node['photo']) }}" alt="{{ $node['name'] }}" class="w-full h-full object-cover">
            @else
                {{ strtoupper(substr($node['name'], 0, 2)) }}
            @endif
        </div>

        <h4 class="font-bold text-slate-800 text-sm line-clamp-1">{{ $node['name'] }}</h4>
        <p class="text-xs font-semibold text-red-600 line-clamp-1 mt-0.5">{{ $node['position'] }}</p>
        <p class="text-[11px] text-slate-400 line-clamp-1">{{ $node['department'] }}</p>

        <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
            <span class="font-mono text-slate-400">{{ $node['employee_number'] }}</span>
            @if($node['grade'])
                <span class="px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded font-semibold">{{ $node['grade'] }}</span>
            @endif
        </div>

        <!-- Direct Reports Badge -->
        @if($node['direct_reports_count'] > 0)
            <div class="mt-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">
                    {{ $node['direct_reports_count'] }} Bawahan Langsung ({{ $node['indirect_reports_count'] }} Tim)
                </span>
            </div>
        @endif
    </div>

    <!-- Connector & Children -->
    @if(count($node['children']) > 0)
        <!-- Vertical connector line down -->
        <div class="w-0.5 h-6 bg-slate-300"></div>

        <!-- Children container -->
        <div class="relative pt-4">
            <!-- Horizontal branch line across children -->
            @if(count($node['children']) > 1)
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[calc(100%-16rem)] h-0.5 bg-slate-300"></div>
            @endif

            <div class="flex items-start justify-center gap-6">
                @foreach($node['children'] as $child)
                    <div class="relative flex flex-col items-center">
                        <!-- Vertical connector line up to horizontal branch -->
                        <div class="w-0.5 h-4 bg-slate-300 absolute -top-4"></div>
                        @include('company.orgchart._node', ['node' => $child])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
