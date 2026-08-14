import { useState, useEffect, useRef } from 'react';

/**
 * Countdown timer hook derived from server-authoritative timestamps.
 * Calculates remaining time as: duration - (now - serverStartedAt)
 * Updates every second and never returns negative values.
 */
export function useTimer(startedAt: string | null, durationSeconds: number): number {
  const [timeRemaining, setTimeRemaining] = useState(durationSeconds);
  const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

  useEffect(() => {
    if (intervalRef.current) {
      clearInterval(intervalRef.current);
      intervalRef.current = null;
    }

    if (!startedAt) {
      setTimeRemaining(durationSeconds);
      return;
    }

    const startTime = new Date(startedAt).getTime();

    const calculateRemaining = () => {
      const elapsed = (Date.now() - startTime) / 1000;
      const remaining = Math.max(0, durationSeconds - elapsed);
      return Math.ceil(remaining);
    };

    setTimeRemaining(calculateRemaining());

    intervalRef.current = setInterval(() => {
      const remaining = calculateRemaining();
      setTimeRemaining(remaining);

      if (remaining <= 0 && intervalRef.current) {
        clearInterval(intervalRef.current);
        intervalRef.current = null;
      }
    }, 1000);

    return () => {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
        intervalRef.current = null;
      }
    };
  }, [startedAt, durationSeconds]);

  return timeRemaining;
}
