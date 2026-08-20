/**
 * Synthesized game sounds using Web Audio API.
 * No external audio files needed.
 */

let audioCtx: AudioContext | null = null;

function getAudioContext(): AudioContext {
  if (!audioCtx) {
    audioCtx = new (window.AudioContext || (window as any).webkitAudioContext)();
  }
  // Resume if suspended (required after user interaction on mobile)
  if (audioCtx.state === 'suspended') {
    audioCtx.resume();
  }
  return audioCtx;
}

function playTone(frequency: number, duration: number, type: OscillatorType = 'sine', volume = 0.3) {
  const ctx = getAudioContext();
  const oscillator = ctx.createOscillator();
  const gainNode = ctx.createGain();

  oscillator.connect(gainNode);
  gainNode.connect(ctx.destination);

  oscillator.type = type;
  oscillator.frequency.setValueAtTime(frequency, ctx.currentTime);

  gainNode.gain.setValueAtTime(volume, ctx.currentTime);
  gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + duration);

  oscillator.start(ctx.currentTime);
  oscillator.stop(ctx.currentTime + duration);
}

/** Short ascending arpeggio — word accepted */
export function playCorrectSound() {
  const ctx = getAudioContext();
  const now = ctx.currentTime;

  [523, 659, 784].forEach((freq, i) => {
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.type = 'sine';
    osc.frequency.setValueAtTime(freq, now + i * 0.08);

    gain.gain.setValueAtTime(0.25, now + i * 0.08);
    gain.gain.exponentialRampToValueAtTime(0.01, now + i * 0.08 + 0.15);

    osc.start(now + i * 0.08);
    osc.stop(now + i * 0.08 + 0.15);
  });
}

/** Quick descending buzz — word rejected */
export function playWrongSound() {
  playTone(200, 0.15, 'square', 0.15);
  setTimeout(() => playTone(150, 0.15, 'square', 0.12), 100);
}

/** Celebratory fanfare — round finished with points */
export function playFinishSound() {
  const ctx = getAudioContext();
  const now = ctx.currentTime;

  const notes = [523, 659, 784, 1047];
  notes.forEach((freq, i) => {
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.type = 'triangle';
    osc.frequency.setValueAtTime(freq, now + i * 0.12);

    gain.gain.setValueAtTime(0.3, now + i * 0.12);
    gain.gain.exponentialRampToValueAtTime(0.01, now + i * 0.12 + 0.3);

    osc.start(now + i * 0.12);
    osc.stop(now + i * 0.12 + 0.3);
  });
}

/** Sad trombone — round finished with zero points */
export function playFailSound() {
  const ctx = getAudioContext();
  const now = ctx.currentTime;

  const notes = [392, 370, 349, 330];
  notes.forEach((freq, i) => {
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.type = 'sawtooth';
    osc.frequency.setValueAtTime(freq, now + i * 0.2);

    gain.gain.setValueAtTime(0.15, now + i * 0.2);
    gain.gain.exponentialRampToValueAtTime(0.01, now + i * 0.2 + 0.25);

    osc.start(now + i * 0.2);
    osc.stop(now + i * 0.2 + 0.25);
  });
}

/** High score sound — word with 50+ points */
export function playHighScoreSound() {
  const ctx = getAudioContext();
  const now = ctx.currentTime;

  const notes = [784, 988, 1175, 1319, 1568];
  notes.forEach((freq, i) => {
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.type = 'sine';
    osc.frequency.setValueAtTime(freq, now + i * 0.06);

    gain.gain.setValueAtTime(0.2, now + i * 0.06);
    gain.gain.exponentialRampToValueAtTime(0.01, now + i * 0.06 + 0.12);

    osc.start(now + i * 0.06);
    osc.stop(now + i * 0.06 + 0.12);
  });
}
