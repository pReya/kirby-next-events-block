panel.plugin("welcome-werkstatt/next-events-block", {
  blocks: {
    "next-events": {
      template: `
        <div v-bind:class="content.color" class="box-container" @click="open">
          <h3 v-if="content.title" class="box-title" v-html="content.title"></h3>
          <div class="box-content" v-html="content.body"></div>
        </div>
      `,
    },
  },
});
