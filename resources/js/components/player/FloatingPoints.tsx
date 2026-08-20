import { useState, useEffect } from 'react';

interface FloatingPoint {
  id: number;
  points: number;
  createdAt: number;
}

interface FloatingPointsProps {
  trigger: { points: number; id: number } | null;
}

export default function FloatingPoints({ trigger }: FloatingPointsProps) {
  const [floats, setFloats] = useState<FloatingPoint[]>([]);

  useEffect(() => {
    if (!trigger || trigger.points <= 0) return;

    const newFloat: FloatingPoint = {
      id: trigger.id,
      points: trigger.points,
      createdAt: Date.now(),
    };

    setFloats((prev) => [...prev, newFloat]);

    // Remove after animation completes
    const timeout = setTimeout(() => {
      setFloats((prev) => prev.filter((f) => f.id !== newFloat.id));
    }, 1200);

    return () => clearTimeout(timeout);
  }, [trigger]);

  return (
    <>
      {floats.map((f) => (
        <div
          key={f.id}
          style={{
            position: 'fixed',
            left: '50%',
            bottom: '120px',
            transform: 'translateX(-50%)',
            zIndex: 999,
            pointerEvents: 'none',
            animation: 'floatUp 1.2s ease-out forwards',
          }}
        >
          <span
            style={{
              fontSize: f.points >= 50 ? '32px' : '24px',
              fontWeight: '800',
              color: f.points >= 50 ? '#fbbf24' : '#4ade80',
              textShadow: '0 2px 8px rgba(0,0,0,0.5)',
            }}
          >
            +{f.points}
          </span>
          {f.points >= 50 && (
            <span style={{ fontSize: '14px', display: 'block', textAlign: 'center', color: '#fbbf24' }}>
              Excelente!
            </span>
          )}
        </div>
      ))}

      <style>{`
        @keyframes floatUp {
          0% {
            opacity: 1;
            transform: translateX(-50%) translateY(0) scale(1);
          }
          50% {
            opacity: 1;
            transform: translateX(-50%) translateY(-60px) scale(1.2);
          }
          100% {
            opacity: 0;
            transform: translateX(-50%) translateY(-120px) scale(0.8);
          }
        }
      `}</style>
    </>
  );
}
