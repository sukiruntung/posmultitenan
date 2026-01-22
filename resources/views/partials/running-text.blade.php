<div class="flex-1 overflow-hidden whitespace-nowrap mx-4">
    <div class="inline-block animate-marquee text-md font-bold dark:text-white"> 
        {{ $text->text ?? '' }}
    </div>
</div>

<style>
@keyframes marquee {
    0% {
        transform: translateX(100%);
    }
    100% {
        transform: translateX(-150%);
    }
}

.animate-marquee {
    animation: marquee 18s linear infinite;
}
</style>
