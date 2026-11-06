<!--
  Matomo - free/libre analytics platform
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later

  Feinwerk feedback kit: a question with thumbs up and down, sent through the plugin's API
  method sendFeedback (the Matomo server talks to Feinwerk, never the browser), and a support
  link in a new tab.
-->
<template>
  <div
    v-if="feedback.enabled || feedback.supportUrl"
    class="fwde-feedback"
  >
    <div class="fwde-feedback__row">
      <template v-if="feedback.enabled">
        <span>{{ question }}</span>
        <button
          v-for="choice in choices"
          :key="choice"
          type="button"
          class="fwde-feedback__thumb"
          :class="{ 'is-chosen': vote === choice }"
          :title="label(choice)"
          :aria-label="label(choice)"
          :aria-pressed="vote === choice ? 'true' : 'false'"
          :disabled="vote !== ''"
          @click="send(choice)"
        >
          <svg
            viewBox="0 0 24 24"
            width="18"
            height="18"
            aria-hidden="true"
            :class="`fwde-feedback__icon--${choice}`"
          >
            <path :d="thumbPath" />
          </svg>
        </button>
      </template>
      <a
        v-if="feedback.supportUrl"
        class="fwde-button fwde-feedback__support"
        :href="feedback.supportUrl"
        target="_blank"
        rel="noopener noreferrer"
        :title="tr('SupportTitle')"
      ><span
        class="icon-help"
        aria-hidden="true"
      /> {{ tr('Support') }}</a>
    </div>
    <p
      v-if="feedback.enabled && vote"
      class="fwde-feedback__thanks"
      role="status"
    >{{ vote === 'up' ? tr('FeedbackThanks') : tr('FeedbackThanksDown') }}</p>
    <p
      v-if="feedback.enabled"
      class="fwde-feedback__privacy"
    >{{ tr('FeedbackPrivacy') }}</p>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { AjaxHelper, translate } from 'CoreHome';
import {
  FEEDBACK_PLUGIN, FeedbackSettings, rememberVote, storedVote, Vote,
} from './feedback';

/** a thumb outline (24 x 24); the down thumb is the same, rotated */
const THUMB_PATH = 'M3 10h4v10H3zM7 10l4-7c1.3 0 2.2 1.1 2 2.4L12.4 9H19a2 2 0 0 1 2 2.3l-1.2 7A2 2 0 0 1 '
  + '17.8 20H7';

function browserStorage(): Storage|null {
  try {
    return window.localStorage;
  } catch (e) {
    return null;
  }
}

export default defineComponent({
  props: {
    idSite: { type: Number, required: true },
    /** the store "topic": one of the plugin's PluginFeedback::TOPICS */
    topic: { type: String, required: true },
    /** the question, already translated */
    question: { type: String, required: true },
    feedback: {
      type: Object as PropType<FeedbackSettings>,
      default: () => ({ enabled: false, supportUrl: '', version: '' }),
    },
  },
  data() {
    const storage = browserStorage();
    return {
      vote: (storage ? storedVote(this.topic, this.feedback.version, storage) : '') as '' | Vote,
    };
  },
  computed: {
    choices(): Vote[] {
      return ['up', 'down'];
    },
    thumbPath(): string {
      return THUMB_PATH;
    },
  },
  methods: {
    tr(key: string): string {
      return translate(`${FEEDBACK_PLUGIN}_${key}`);
    },
    label(choice: Vote): string {
      return choice === 'up' ? this.tr('FeedbackYes') : this.tr('FeedbackNo');
    },
    send(vote: Vote) {
      this.vote = vote;
      const storage = browserStorage();
      if (storage) {
        rememberVote(this.topic, this.feedback.version, vote, storage);
      }
      // Best effort: the vote is a hint for Feinwerk, a failed request needs no error message.
      AjaxHelper.post(
        { method: `${FEEDBACK_PLUGIN}.sendFeedback`, idSite: this.idSite },
        { topic: this.topic, vote },
        { createErrorNotification: false },
      ).catch(() => undefined);
    },
  },
});
</script>
