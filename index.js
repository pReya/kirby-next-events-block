panel.plugin("preya/kirby-next-events-block", {
  blocks: {
    "next-events": {
      template: `
        <div @click="open">
          <h3>Next Events</h3>
          <ul>
            <li>Placeholder event 1</li>
            <li>Placeholder event 2</li>
            <li>Placeholder event 3</li>
          </ul>
        </div>
      `,
    },
  },
});
