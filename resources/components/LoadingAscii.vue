<template>
    <div>
        {{ currentArt.frames[currentFrame] }}
    </div>
</template>

<script>
import { ref } from 'vue';

export default {
    props: ['art', 'timeout'],
    setup() {
        return {
            currentFrame: ref(null),
            newArts: require('cli-spinners/spinners.json'),
            interval: ref(null),
        }
    },
    computed: {
        currentArt() {
            return this.newArts[this.seed];
        },
        seed() {
            if (this.art) {
                return this.art;
            }

            const values = Object.values(this.newArts)
            const index = (~~(values.length * Math.random()) );
            return Object.keys(this.newArts)[index];
        }
    },
    mounted () {
        this.interval = setInterval(() => {
            const maxFrames = this.currentArt?.frames?.length
            this.currentFrame = this.currentFrame >= maxFrames - 1 ? 0 : this.currentFrame + 1
        }, this.currentArt?.interval);
    },
    beforeDestroy () {
        clearInterval(this.interval);
    },
}
</script>
