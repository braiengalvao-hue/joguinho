@extends('layouts.arcade')

@section('title', 'Snake - Arcade')

@section('content')
<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl relative w-full max-w-2xl text-center border-4 border-indigo-500">
    <a href="{{ route('arcade.index') }}" class="absolute -top-4 -right-4 bg-red-600 text-white w-10 h-10 rounded-full font-bold text-xl border-2 border-white hover:bg-red-700 flex items-center justify-center decoration-transparent">&times;</a>
    
    <h2 class="text-3xl font-black mb-4 dark:text-white uppercase text-indigo-600 dark:text-indigo-400">Snake</h2>
    
    <canvas id="gc" width="400" height="400"></canvas>
</div>
@endsection

@push('scripts')
<script>
    // O script do Snake roda automaticamente quando a página carrega
    window.onload = function() {
        let ctx = document.getElementById('gc').getContext('2d');
        let px=10, py=10, gs=20, tc=20, ax=15, ay=15, xv=0, yv=0, trail=[], tail=5, score=0;
        
        document.onkeydown = e => { 
            if(e.key=='ArrowLeft' && xv!=1) {xv=-1;yv=0;} 
            if(e.key=='ArrowUp' && yv!=1) {xv=0;yv=-1;} 
            if(e.key=='ArrowRight' && xv!=-1) {xv=1;yv=0;} 
            if(e.key=='ArrowDown' && yv!=-1) {xv=0;yv=1;} 
        };
        
        setInterval(() => {
            px+=xv; py+=yv;
            if(px<0) px=tc-1; if(px>tc-1) px=0; if(py<0) py=tc-1; if(py>tc-1) py=0;
            ctx.fillStyle="black"; ctx.fillRect(0,0,400,400); ctx.fillStyle="lime";
            for(let i=0;i<trail.length;i++){ 
                ctx.fillRect(trail[i].x*gs,trail[i].y*gs,gs-2,gs-2); 
                if(trail[i].x==px && trail[i].y==py && tail>5) { tail=5; score=0; } 
            }
            trail.push({x:px,y:py}); while(trail.length>tail) trail.shift();
            if(ax==px && ay==py) { tail++; score+=10; ax=Math.floor(Math.random()*tc); ay=Math.floor(Math.random()*tc); }
            ctx.fillStyle="red"; ctx.fillRect(ax*gs,ay*gs,gs-2,gs-2);
            ctx.fillStyle="white"; ctx.fillText("Score: "+score, 10, 20);
        }, 100);
    };
</script>
@endpush